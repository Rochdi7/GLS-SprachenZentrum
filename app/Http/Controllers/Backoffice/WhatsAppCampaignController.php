<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use App\Models\WhatsAppCampaign;
use App\Services\WhatsApp\CampaignRuntime;
use App\Services\WhatsApp\WindowsAutomation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsAppCampaignController extends Controller
{
    use ScopesToUserSites;

    public function __construct(private readonly CampaignRuntime $runtime) {}

    public function index(Request $request)
    {
        // Default filter to the logged-in user's primary centre unless they
        // explicitly chose another filter (including "all" via ?site_id=all).
        $userSiteId = auth()->user()->site_id ?? null;
        if ($request->has('site_id')) {
            $siteId = $request->input('site_id');
        } else {
            $siteId = $userSiteId ? (string) $userSiteId : '';
        }

        $query = WhatsAppCampaign::with(['user', 'site'])->latest();

        // Non-admin users: hard-scope to centres they're affected to.
        $this->scopeToUserSites($query);

        // Apply explicit site_id filter — but only if the user can access it.
        if ($siteId !== null && $siteId !== '' && $siteId !== 'all') {
            if ($siteId === 'none') {
                $query->whereNull('site_id');
            } else {
                $resolved = $this->resolveRequestedSiteId((int) $siteId);
                if ($resolved !== null) {
                    $query->where('site_id', $resolved);
                } elseif (! $this->userSeesAllSites()) {
                    // Asked a centre they can't see → empty results
                    $query->whereRaw('1 = 0');
                }
            }
        }

        $campaigns = $query->get();
        $sites = $this->accessibleSites();

        return view('backoffice.whatsapp_campaigns.index', compact('campaigns', 'sites', 'siteId'));
    }

    public function dashboard()
    {
        $totals = [
            'campaigns' => WhatsAppCampaign::count(),
            'recipients'=> (int) WhatsAppCampaign::sum('total'),
            'sent'      => (int) WhatsAppCampaign::sum('sent'),
            'failed'    => (int) WhatsAppCampaign::sum('failed'),
        ];
        $totals['pending'] = max(0, $totals['recipients'] - $totals['sent'] - $totals['failed']);
        $totals['success_rate'] = $totals['recipients']
            ? round($totals['sent'] * 100 / $totals['recipients'], 1)
            : 0;

        $statusCounts = WhatsAppCampaign::select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();
        $statusCounts = array_merge([
            'queued' => 0, 'running' => 0, 'paused' => 0,
            'completed' => 0, 'stopped' => 0,
        ], $statusCounts);

        // Last 14 days — sent / failed per day
        $days = collect();
        for ($i = 13; $i >= 0; $i--) {
            $days->push(now()->subDays($i)->format('Y-m-d'));
        }
        $daily = WhatsAppCampaign::where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as d, SUM(sent) as sent, SUM(failed) as failed, COUNT(*) as campaigns')
            ->groupBy('d')
            ->get()
            ->keyBy('d');
        $dailySeries = $days->map(function ($d) use ($daily) {
            $row = $daily->get($d);
            return [
                'date'      => $d,
                'sent'      => (int) ($row->sent ?? 0),
                'failed'    => (int) ($row->failed ?? 0),
                'campaigns' => (int) ($row->campaigns ?? 0),
            ];
        })->values();

        // Per-site ranking
        $perSite = WhatsAppCampaign::selectRaw('
                site_id,
                COUNT(*) as campaigns,
                SUM(total) as recipients,
                SUM(sent) as sent,
                SUM(failed) as failed
            ')
            ->groupBy('site_id')
            ->orderByDesc('campaigns')
            ->get()
            ->map(function ($row) {
                $site = $row->site_id ? Site::find($row->site_id) : null;
                return [
                    'site_id'     => $row->site_id,
                    'site_name'   => $site?->name ?? '— Non assigné —',
                    'campaigns'   => (int) $row->campaigns,
                    'recipients'  => (int) $row->recipients,
                    'sent'        => (int) $row->sent,
                    'failed'      => (int) $row->failed,
                ];
            });

        // Top users
        $perUser = WhatsAppCampaign::selectRaw('
                user_id,
                COUNT(*) as campaigns,
                SUM(sent) as sent
            ')
            ->groupBy('user_id')
            ->orderByDesc('campaigns')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $user = $row->user_id ? User::find($row->user_id) : null;
                return [
                    'user_id'   => $row->user_id,
                    'user_name' => $user?->name ?? '— Inconnu —',
                    'campaigns' => (int) $row->campaigns,
                    'sent'      => (int) $row->sent,
                ];
            });

        $recent = WhatsAppCampaign::with(['user', 'site'])->latest()->limit(10)->get();

        return view('backoffice.whatsapp_campaigns.dashboard', compact(
            'totals', 'statusCounts', 'dailySeries', 'perSite', 'perUser', 'recent'
        ));
    }

    public function create()
    {
        $sites = $this->accessibleSites();
        return view('backoffice.whatsapp_campaigns.create', compact('sites'));
    }

    public function show(WhatsAppCampaign $campaign)
    {
        return view('backoffice.whatsapp_campaigns.show', compact('campaign'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'site_id'     => 'nullable|exists:sites,id',
            'message'     => 'required|string',
            'numbers'     => 'required|string',
            'delay_min'   => 'nullable|integer|min:30',
            'delay_max'   => 'nullable|integer|min:40',
            'launch_wait' => 'nullable|integer|min:3|max:30',
            'attachment'  => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png,webp,mp4',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $dir = storage_path('app/whatsapp-attachments');
            if (!is_dir($dir)) @mkdir($dir, 0o777, true);
            $file = $request->file('attachment');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move($dir, $filename);
            $attachmentPath = $dir . DIRECTORY_SEPARATOR . $filename;
        }

        $lines = preg_split('/\r?\n/', trim($data['numbers']));
        $recipients = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $parts = array_map('trim', explode(',', $line));
            $recipients[] = [
                'phone'    => $parts[0],
                'business' => $parts[1] ?? '',
                'status'   => 'pending',
                'error'    => null,
                'sentAt'   => null,
            ];
        }
        if (!count($recipients)) {
            return back()->withInput()->with('error', 'Aucun numéro valide.');
        }

        $minD = max(30, (int) ($data['delay_min'] ?? 45));
        $maxD = max($minD + 10, (int) ($data['delay_max'] ?? 90));
        $launchWait = max(3, (int) ($data['launch_wait'] ?? 7));

        $campaign = WhatsAppCampaign::create([
            'site_id'         => $data['site_id'] ?? auth()->user()->site_id ?? null,
            'user_id'         => auth()->id(),
            'name'            => $data['name'],
            'message'         => $data['message'],
            'status'          => 'queued',
            'total'           => count($recipients),
            'sent'            => 0,
            'failed'          => 0,
            'delay_min'       => $minD,
            'delay_max'       => $maxD,
            'launch_wait'     => $launchWait,
            'attachment_path' => $attachmentPath,
            'recipients'      => $recipients,
        ]);

        return redirect()->route('backoffice.whatsapp_campaigns.show', $campaign)
            ->with('success', 'Campagne créée. Cliquez sur "Démarrer" pour lancer l\'envoi.');
    }

    public function destroy(Request $request, WhatsAppCampaign $campaign)
    {
        $s = $this->runtime->readStatus();
        $isThis = (int) ($s['campaignId'] ?? 0) === $campaign->id;

        if (!empty($s['running']) && $isThis) {
            // Allow an explicit force-delete: stop the worker and purge the lock.
            if ($request->boolean('force')) {
                $this->runtime->writeControl(['paused' => false, 'aborted' => true]);
                $this->runtime->clearStatus();
            } else {
                return back()->with('error', 'Impossible de supprimer une campagne en cours. Arrêtez-la d\'abord, ou utilisez "Forcer la suppression".');
            }
        } elseif ($isThis) {
            // Lock file still points at this campaign but isn't running (stale
            // or finished) — purge it so a future start isn't confused.
            $this->runtime->clearStatus();
        }

        if ($campaign->attachment_path && is_file($campaign->attachment_path)) {
            @unlink($campaign->attachment_path);
        }
        $campaign->delete();
        return redirect()->route('backoffice.whatsapp_campaigns.index')
            ->with('success', 'Campagne supprimée.');
    }

    // --- Live control endpoints (JSON) -------------------------------------

    public function start(WhatsAppCampaign $campaign): JsonResponse
    {
        $status = $this->runtime->readStatus();
        if (!empty($status['running'])) {
            return response()->json([
                'error' => 'Une campagne est déjà en cours',
                'runningCampaignId' => $status['campaignId'] ?? null,
            ], 409);
        }
        // readStatus() already flags stale locks as not-running, but purge
        // the leftover file so subsequent reads are clean.
        if (!empty($status['stale'])) {
            $this->runtime->clearStatus();
            // Any previous campaign still in "running" state in DB is also a
            // zombie — the worker died without finalizing.
            WhatsAppCampaign::where('status', 'running')->update(['status' => 'paused']);
        }

        $attach = (string) ($campaign->attachment_path ?? '');
        if ($attach !== '' && !is_file($attach)) {
            return response()->json(['error' => "Fichier non trouvé: {$attach}"], 400);
        }

        $recipients = $campaign->recipients ?? [];
        foreach ($recipients as &$r) {
            if (($r['status'] ?? '') === 'sending') $r['status'] = 'pending';
        }
        unset($r);

        $pending = 0;
        foreach ($recipients as $r) if (($r['status'] ?? '') === 'pending') $pending++;
        if ($pending === 0) {
            return response()->json(['error' => 'Aucun message en attente'], 400);
        }

        $campaign->recipients = $recipients;
        $campaign->status = 'queued';
        $campaign->save();

        $this->runtime->writeStatus([
            'running'        => true,
            'campaignId'     => $campaign->id,
            'name'           => $campaign->name,
            'status'         => 'queued',
            'total'          => $campaign->total,
            'sent'           => $campaign->sent,
            'failed'         => $campaign->failed,
            'pending'        => $pending,
            'current'        => null,
            'nextSendAt'     => null,
            'attachmentPath' => $campaign->attachment_path,
            'messages'       => $recipients,
        ]);
        $this->runtime->writeControl(['paused' => false, 'aborted' => false]);

        try {
            $this->spawnWorker($campaign->id);
        } catch (\Throwable $e) {
            // Roll back the lock so the user isn't stuck.
            $this->runtime->clearStatus();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'started'    => true,
            'campaignId' => $campaign->id,
            'pending'    => $pending,
        ]);
    }

    public function status(WhatsAppCampaign $campaign): JsonResponse
    {
        $s = $this->runtime->readStatus();
        $isThis = (int) ($s['campaignId'] ?? 0) === $campaign->id;

        if (!$isThis) {
            $recipients = $campaign->recipients ?? [];
            $pending = 0;
            foreach ($recipients as $r) if (($r['status'] ?? '') === 'pending') $pending++;
            return response()->json([
                'running'    => false,
                'campaignId' => $campaign->id,
                'name'       => $campaign->name,
                'status'     => $campaign->status,
                'total'      => $campaign->total,
                'sent'       => $campaign->sent,
                'failed'     => $campaign->failed,
                'pending'    => $pending,
                'current'    => null,
                'messages'   => $recipients,
            ]);
        }

        // Load fresh recipients from DB in case the tick file is stale.
        $campaign->refresh();
        $s['messages'] = $campaign->recipients ?? ($s['messages'] ?? []);
        if (!empty($s['nextSendAt'])) {
            $s['next_send_in'] = max(0, ((int) $s['nextSendAt']) - time());
        }
        $s['sent']   = $campaign->sent;
        $s['failed'] = $campaign->failed;
        $s['total']  = $campaign->total;

        $control = $this->runtime->readControl();
        $s['paused']  = (bool) ($control['paused'] ?? false);
        $s['aborted'] = (bool) ($control['aborted'] ?? false);

        return response()->json($s);
    }

    public function pause(): JsonResponse
    {
        $c = $this->runtime->readControl();
        $c['paused'] = true;
        $this->runtime->writeControl($c);
        return response()->json(['paused' => true]);
    }

    public function resume(): JsonResponse
    {
        $c = $this->runtime->readControl();
        $c['paused'] = false;
        $this->runtime->writeControl($c);
        return response()->json(['resumed' => true]);
    }

    public function stop(): JsonResponse
    {
        $c = $this->runtime->readControl();
        $c['aborted'] = true;
        $c['paused']  = false;
        $this->runtime->writeControl($c);
        return response()->json(['stopped' => true]);
    }

    /**
     * Clear a stuck "running" lock. Used when a worker died without writing
     * its final status (e.g. server restart, PHP crash), leaving every new
     * start attempt blocked by a stale lock.
     */
    public function forceReset(): JsonResponse
    {
        $this->runtime->clearStatus();
        $this->runtime->writeControl(['paused' => false, 'aborted' => false]);
        WhatsAppCampaign::where('status', 'running')->update(['status' => 'paused']);
        return response()->json(['reset' => true]);
    }

    /**
     * List of phone numbers (normalized intl-no-plus) that have status='sent'
     * in ANY prior campaign — used by the create form to warn about duplicates.
     */
    public function contactedPhones(): JsonResponse
    {
        $seen = [];
        WhatsAppCampaign::select('recipients')->chunk(200, function ($chunk) use (&$seen) {
            foreach ($chunk as $c) {
                foreach (($c->recipients ?? []) as $r) {
                    if (($r['status'] ?? '') !== 'sent') continue;
                    $p = WindowsAutomation::cleanPhone((string) ($r['phone'] ?? ''));
                    if ($p !== '') $seen[$p] = true;
                }
            }
        });
        return response()->json(['phones' => array_keys($seen), 'count' => count($seen)]);
    }

    /**
     * Tail the worker log for a campaign — last N lines. Used by the show page
     * to surface crashes / stuck sends without SSH.
     */
    public function log(WhatsAppCampaign $campaign): JsonResponse
    {
        $path = storage_path('app/wa-worker-' . $campaign->id . '.log');
        if (!is_file($path)) {
            return response()->json([
                'exists' => false,
                'lines'  => [],
                'size'   => 0,
            ]);
        }
        $size = filesize($path);
        // Read the tail — cap at 64 KB to stay light.
        $max = 64 * 1024;
        $offset = max(0, $size - $max);
        $fp = fopen($path, 'rb');
        if ($offset > 0) fseek($fp, $offset);
        $content = fread($fp, $max);
        fclose($fp);
        $lines = preg_split('/\r?\n/', (string) $content);
        $tail = array_slice(array_filter($lines, fn ($l) => $l !== ''), -80);
        return response()->json([
            'exists' => true,
            'size'   => $size,
            'lines'  => array_values($tail),
        ]);
    }

    /**
     * Spawn the worker as a detached background process on Windows.
     *
     * Tries several mechanisms because shared/locked-down PHP installs often
     * disable some of popen/pclose/exec/shell_exec/proc_open. We only need ONE
     * of them to work. Order: proc_open (cleanest) → COM WScript.Shell (no
     * disable_functions involvement) → Symfony Process (uses proc_open) →
     * a clear error if everything is off.
     */
    private function spawnWorker(int $campaignId): void
    {
        $php       = PHP_BINARY;
        $artisan   = base_path('artisan');
        $batPath   = storage_path('app/wa-run-' . $campaignId . '.bat');
        $logPath   = storage_path('app/wa-spawn.log');
        $workerLog = storage_path('app/wa-worker-' . $campaignId . '.log');

        $dir = dirname($batPath);
        if (!is_dir($dir)) @mkdir($dir, 0o777, true);

        $bat  = "@echo off\r\n";
        $bat .= '"' . $php . '" "' . $artisan . '" wa:run-campaign ' . $campaignId
              . ' > "' . $workerLog . '" 2>&1' . "\r\n";
        file_put_contents($batPath, $bat);

        $cmd    = 'cmd /c start "" /B "' . $batPath . '"';
        $method = null;
        $err    = null;

        try {
            if ($this->funcEnabled('proc_open')) {
                $p = @proc_open($cmd, [
                    0 => ['pipe', 'r'],
                    1 => ['file', 'NUL', 'w'],
                    2 => ['file', 'NUL', 'w'],
                ], $pipes, null, null, ['bypass_shell' => true]);
                if (is_resource($p)) {
                    foreach ($pipes as $pipe) { if (is_resource($pipe)) fclose($pipe); }
                    proc_close($p);
                    $method = 'proc_open';
                }
            }

            if ($method === null && class_exists('COM')) {
                try {
                    $shell = new \COM('WScript.Shell');
                    // Run() args: cmd, windowStyle (0=hidden), waitOnReturn(false)
                    $shell->Run($cmd, 0, false);
                    $method = 'wscript.shell';
                } catch (\Throwable $e) {
                    $err = 'COM failed: ' . $e->getMessage();
                }
            }

            if ($method === null) {
                try {
                    $p = \Symfony\Component\Process\Process::fromShellCommandline($cmd);
                    $p->disableOutput();
                    $p->start();
                    $method = 'symfony.process';
                } catch (\Throwable $e) {
                    $err = 'Symfony Process failed: ' . $e->getMessage();
                }
            }
        } catch (\Throwable $e) {
            $err = $e->getMessage();
        }

        file_put_contents(
            $logPath,
            sprintf(
                "[%s] spawn %s via=%s err=%s\n  bat=%s\n  worker-log=%s\n",
                gmdate('c'), $campaignId, $method ?? 'NONE', $err ?? '-', $batPath, $workerLog
            ),
            FILE_APPEND | LOCK_EX
        );

        if ($method === null) {
            throw new \RuntimeException(
                'Impossible de démarrer le worker WhatsApp : toutes les méthodes de spawn sont bloquées ' .
                '(proc_open/COM/Symfony Process). Vérifiez php.ini → disable_functions sur le serveur.'
            );
        }
    }

    private function funcEnabled(string $fn): bool
    {
        if (!function_exists($fn)) return false;
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        return !in_array($fn, $disabled, true);
    }
}

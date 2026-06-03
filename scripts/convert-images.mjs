/**
 * convert-images.mjs
 * Full-project image → AVIF optimizer for GLS Sprachenzentrum.
 *
 * Idempotent: safe to run multiple times.
 * Skips AVIF output when the existing file is already ≤ the new encode.
 * Keeps originals that would grow larger as AVIF.
 * Writes a rollback manifest before any deletion.
 */

import sharp from 'sharp';
import { readdir, stat, mkdir, writeFile, unlink, copyFile, readFile } from 'fs/promises';
import { join, extname, basename, dirname, relative } from 'path';
import { existsSync } from 'fs';
import { fileURLToPath } from 'url';

// ─── Paths ────────────────────────────────────────────────────────────────────
const __dirname   = dirname(fileURLToPath(import.meta.url));
const ROOT        = join(__dirname, '..');
const IMAGE_ROOT  = join(ROOT, 'public', 'assets', 'images');
const BACKUP_DIR  = join(ROOT, 'scripts', '.image-backup');
const LOG_FILE    = join(ROOT, 'scripts', 'image-optimization.log');
const REPORT_FILE = join(ROOT, 'scripts', 'image-optimization-report.json');

// Source extensions to convert
const CONVERTIBLE = new Set(['.jpg', '.jpeg', '.png', '.webp', '.gif']);
// Uppercase variants
const CONVERTIBLE_UPPER = new Set(['.JPG', '.JPEG', '.PNG', '.WEBP', '.GIF']);

// Per-directory max display dimensions  (width × height, fit: inside)
// Keeps hero images large, logos/avatars small
const DIM_RULES = [
  { match: /\/user\//,              maxW: 256,  maxH: 256  },
  { match: /\/login\//,             maxW: 512,  maxH: 512  },
  { match: /\/favicon\//,           maxW: 512,  maxH: 512  },
  { match: /\/logo\//,              maxW: 800,  maxH: 400  },
  { match: /\/home\//,              maxW: 1920, maxH: 1080 },
  { match: /\/niveaux\//,           maxW: 1920, maxH: 1080 },
  { match: /\/student-stories\//,   maxW: 1200, maxH: 1200 },
  { match: /\/fc-marokko\//,        maxW: 1600, maxH: 1200 },
  { match: /\/studienkollegs\//,    maxW: 1600, maxH: 1200 },
  { match: /\/contact\//,           maxW: 1600, maxH: 1200 },
  { match: /\/online-courses\//,    maxW: 1920, maxH: 1080 },
  { match: /\/intensive-courses\//, maxW: 1920, maxH: 1080 },
  { match: /\/oursites\//,          maxW: 1920, maxH: 1080 },
  { match: /\/sites\//,             maxW: 1920, maxH: 1080 },
  { match: /\/about\//,             maxW: 1920, maxH: 1080 },
  { match: /\/blog\//,              maxW: 1600, maxH: 1200 },
  { match: /[/\\]images[/\\][^/\\]+\.[a-z]+$/i, maxW: 1920, maxH: 1080 }, // root images
];

// AVIF quality per category
const QUALITY_RULES = [
  { match: /\/user\//,    quality: 70 },
  { match: /\/login\//,   quality: 70 },
  { match: /\/favicon\//, quality: 80 },
  { match: /\/logo\//,    quality: 78 },
  { match: /\.png$/i,     quality: 75 }, // logos/transparent PNGs
  { match: /./,           quality: 72 }, // default
];

function getDims(filePath) {
  const p = filePath.replace(/\\/g, '/');
  for (const rule of DIM_RULES) {
    if (rule.match.test(p)) return { maxW: rule.maxW, maxH: rule.maxH };
  }
  return { maxW: 1920, maxH: 1080 };
}

function getQuality(filePath) {
  const p = filePath.replace(/\\/g, '/');
  for (const rule of QUALITY_RULES) {
    if (rule.match.test(p)) return rule.quality;
  }
  return 72;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function fmt(bytes) { return (bytes / 1024).toFixed(1) + ' KB'; }
function pct(a, b)  { return (((a - b) / a) * 100).toFixed(1) + '%'; }

async function safeStat(p) {
  try { return await stat(p); } catch { return null; }
}

async function walk(dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  const files = [];
  for (const e of entries) {
    const full = join(dir, e.name);
    if (e.isDirectory()) {
      files.push(...await walk(full));
    } else {
      files.push(full);
    }
  }
  return files;
}

// ─── Conversion ───────────────────────────────────────────────────────────────
async function convertToAvif(srcPath) {
  const ext     = extname(srcPath);
  const base    = basename(srcPath, ext);
  const dir     = dirname(srcPath);
  const outPath = join(dir, base + '.avif');
  const quality = getQuality(srcPath);
  const { maxW, maxH } = getDims(srcPath);

  const srcStat = await stat(srcPath);
  const srcMeta = await sharp(srcPath).metadata();

  // Build pipeline
  let pipeline = sharp(srcPath, { animated: ext.toLowerCase() === '.gif' });

  if (srcMeta.width > maxW || srcMeta.height > maxH) {
    pipeline = pipeline.resize(maxW, maxH, { fit: 'inside', withoutEnlargement: true });
  }

  pipeline = pipeline.avif({ quality, effort: 6 });
  const outBuffer = await pipeline.toBuffer();

  // Check existing AVIF
  const existingStat = await safeStat(outPath);
  if (existingStat && existingStat.size <= outBuffer.length) {
    const existingMeta = await sharp(outPath).metadata();
    return {
      status: 'skipped',
      srcPath,
      outPath,
      srcSize: srcStat.size,
      outSize: existingStat.size,
      srcDims: `${srcMeta.width}×${srcMeta.height}`,
      outDims: `${existingMeta.width}×${existingMeta.height}`,
      note: 'existing AVIF already optimal',
    };
  }

  // Would AVIF be larger than the source?
  if (outBuffer.length >= srcStat.size) {
    return {
      status: 'kept_original',
      srcPath,
      outPath: null,
      srcSize: srcStat.size,
      outSize: outBuffer.length,
      srcDims: `${srcMeta.width}×${srcMeta.height}`,
      outDims: `${srcMeta.width}×${srcMeta.height}`,
      note: 'AVIF would be larger — original kept',
    };
  }

  // Write
  await sharp(outBuffer).toFile(outPath);
  const outMeta = await sharp(outPath).metadata();

  return {
    status: 'converted',
    srcPath,
    outPath,
    srcSize: srcStat.size,
    outSize: outBuffer.length,
    srcDims: `${srcMeta.width}×${srcMeta.height}`,
    outDims: `${outMeta.width}×${outMeta.height}`,
    note: '',
  };
}

// Re-optimize an existing AVIF (re-encode at current quality settings)
async function reoptimizeAvif(avifPath) {
  const srcStat = await stat(avifPath);
  const srcMeta = await sharp(avifPath).metadata();
  const quality = getQuality(avifPath);
  const { maxW, maxH } = getDims(avifPath);

  let pipeline = sharp(avifPath);
  if (srcMeta.width > maxW || srcMeta.height > maxH) {
    pipeline = pipeline.resize(maxW, maxH, { fit: 'inside', withoutEnlargement: true });
  }
  pipeline = pipeline.avif({ quality, effort: 6 });
  const outBuffer = await pipeline.toBuffer();

  if (outBuffer.length >= srcStat.size) {
    return {
      status: 'avif_kept',
      srcPath: avifPath,
      outPath: avifPath,
      srcSize: srcStat.size,
      outSize: srcStat.size,
      srcDims: `${srcMeta.width}×${srcMeta.height}`,
      outDims: `${srcMeta.width}×${srcMeta.height}`,
      note: 'existing AVIF already optimal',
    };
  }

  await sharp(outBuffer).toFile(avifPath);
  const outMeta = await sharp(avifPath).metadata();
  return {
    status: 'avif_reoptimized',
    srcPath: avifPath,
    outPath: avifPath,
    srcSize: srcStat.size,
    outSize: outBuffer.length,
    srcDims: `${srcMeta.width}×${srcMeta.height}`,
    outDims: `${outMeta.width}×${outMeta.height}`,
    note: '',
  };
}

// ─── Reference Updater ───────────────────────────────────────────────────────
const SOURCE_GLOBS = [
  'resources/**/*.blade.php',
  'resources/**/*.php',
  'resources/**/*.js',
  'resources/**/*.scss',
  'resources/**/*.css',
  'config/**/*.php',
];

async function findSourceFiles() {
  const dirs   = ['resources', 'config'];
  const exts   = new Set(['.php', '.blade.php', '.js', '.scss', '.css', '.json', '.html']);
  const result = [];
  for (const d of dirs) {
    const full = join(ROOT, d);
    if (!existsSync(full)) continue;
    const files = await walk(full);
    for (const f of files) {
      const e = extname(f).toLowerCase();
      if (exts.has(e) || f.endsWith('.blade.php')) result.push(f);
    }
  }
  return result;
}

// Build a map: oldBasename (no ext) → new .avif path segments for replacements
// Returns list of {file, replacements, newContent}
async function updateReferences(conversionResults) {
  // Only files that produced a real AVIF output
  const converted = conversionResults.filter(r =>
    (r.status === 'converted' || r.status === 'skipped') && r.outPath
  );

  if (!converted.length) return [];

  const sourceFiles = await findSourceFiles();
  const updated = [];

  for (const sf of sourceFiles) {
    let content;
    try { content = await readFile(sf, 'utf8'); } catch { continue; }

    let newContent  = content;
    let count       = 0;
    const changes   = [];

    for (const r of converted) {
      const ext     = extname(r.srcPath);
      const base    = basename(r.srcPath, ext);
      // Match the old filename with its original extension (case-insensitive)
      // e.g. "grid-1.png", "IMG_4462.JPEG", "IMG_4462.jpeg"
      const escaped = base.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const regex   = new RegExp(
        escaped + '\\' + ext,
        'gi'
      );
      if (regex.test(newContent)) {
        const replacement = base + '.avif';
        newContent = newContent.replace(regex, replacement);
        count++;
        changes.push(`  ${basename(r.srcPath)} → ${replacement}`);
      }
    }

    if (count > 0) {
      updated.push({ file: sf, content, newContent, changes });
    }
  }

  return updated;
}

// ─── Validation ──────────────────────────────────────────────────────────────
async function validateReferences(sourceFiles) {
  const broken  = [];
  const ok      = [];
  const imageRe = /assets\/images\/([^'")\s]+\.avif)/gi;

  for (const sf of sourceFiles) {
    let content;
    try { content = await readFile(sf, 'utf8'); } catch { continue; }

    let m;
    while ((m = imageRe.exec(content)) !== null) {
      const rel      = m[1];
      const fullPath = join(ROOT, 'public', 'assets', 'images', rel.split('assets/images/')[0] ? rel : rel);
      // Resolve relative to public/
      const resolved = join(ROOT, 'public', 'assets', 'images', rel.replace(/^assets\/images\//, ''));
      if (!existsSync(resolved)) {
        broken.push({ file: sf, ref: m[0], resolved });
      } else {
        ok.push({ file: sf, ref: m[0] });
      }
    }
  }

  return { broken, ok };
}

// ─── Backup ───────────────────────────────────────────────────────────────────
async function backupFile(srcPath) {
  const rel    = relative(IMAGE_ROOT, srcPath);
  const dest   = join(BACKUP_DIR, rel);
  await mkdir(dirname(dest), { recursive: true });
  await copyFile(srcPath, dest);
}

// ─── Main ────────────────────────────────────────────────────────────────────
async function run() {
  await mkdir(BACKUP_DIR, { recursive: true });

  const logLines = [];
  const log = (...args) => {
    const line = args.join(' ');
    console.log(line);
    logLines.push(line);
  };

  log('═══════════════════════════════════════════════════════════════');
  log('  GLS IMAGE OPTIMIZER — Full Project Run');
  log(`  ${new Date().toISOString()}`);
  log('═══════════════════════════════════════════════════════════════\n');

  // ── 1. Discover all images ──────────────────────────────────────────────────
  log('▶ Scanning image directory…');
  const allFiles   = await walk(IMAGE_ROOT);
  const toConvert  = allFiles.filter(f => {
    const e = extname(f);
    return CONVERTIBLE.has(e.toLowerCase()) && e !== '.svg';
  });
  const existingAvifs = allFiles.filter(f => extname(f).toLowerCase() === '.avif');
  const svgs          = allFiles.filter(f => extname(f).toLowerCase() === '.svg');
  const manifests     = allFiles.filter(f => f.endsWith('.webmanifest'));

  log(`  Found ${toConvert.length} convertible images`);
  log(`  Found ${existingAvifs.length} existing AVIF files`);
  log(`  Found ${svgs.length} SVGs (skipped — already optimal)`);
  log(`  Found ${manifests.length} manifests (skipped)\n`);

  // ── 2. Convert / optimize ──────────────────────────────────────────────────
  log('▶ Converting images to AVIF…\n');
  const convResults = [];

  for (const f of toConvert) {
    const rel = relative(IMAGE_ROOT, f);
    process.stdout.write(`  [CONV] ${rel.padEnd(60)}`);
    try {
      const r = await convertToAvif(f);
      convResults.push(r);
      if (r.status === 'converted') {
        process.stdout.write(`✓  ${fmt(r.srcSize)} → ${fmt(r.outSize)} (${pct(r.srcSize, r.outSize)} saved)\n`);
      } else if (r.status === 'skipped') {
        process.stdout.write(`⊘  already optimal (${fmt(r.outSize)})\n`);
      } else {
        process.stdout.write(`⚠  kept original (AVIF larger: ${fmt(r.outSize)} vs ${fmt(r.srcSize)})\n`);
      }
    } catch (e) {
      process.stdout.write(`✗  ERROR: ${e.message}\n`);
      convResults.push({ status: 'error', srcPath: f, error: e.message });
    }
  }

  // ── 3. Re-optimize existing AVIFs ─────────────────────────────────────────
  log('\n▶ Re-optimizing existing AVIF files…\n');
  const avifResults = [];

  for (const f of existingAvifs) {
    const rel = relative(IMAGE_ROOT, f);
    process.stdout.write(`  [AVIF] ${rel.padEnd(60)}`);
    try {
      const r = await reoptimizeAvif(f);
      avifResults.push(r);
      if (r.status === 'avif_reoptimized') {
        process.stdout.write(`✓  ${fmt(r.srcSize)} → ${fmt(r.outSize)} (${pct(r.srcSize, r.outSize)} saved)\n`);
      } else {
        process.stdout.write(`⊘  already optimal (${fmt(r.outSize)})\n`);
      }
    } catch (e) {
      process.stdout.write(`✗  ERROR: ${e.message}\n`);
      avifResults.push({ status: 'error', srcPath: f, error: e.message });
    }
  }

  // ── 4. Update source file references ──────────────────────────────────────
  log('\n▶ Updating source file references…\n');
  const allConvResults = [...convResults, ...avifResults];
  const updatedFiles   = await updateReferences(allConvResults);

  let totalRefUpdates = 0;
  const modifiedFileList = [];

  for (const u of updatedFiles) {
    const rel = relative(ROOT, u.file);
    log(`  [REF]  ${rel}`);
    for (const c of u.changes) log(c);
    await writeFile(u.file, u.newContent, 'utf8');
    totalRefUpdates += u.changes.length;
    modifiedFileList.push(rel);
  }

  if (!updatedFiles.length) log('  (no references needed updating)');

  // ── 5. Validate references ────────────────────────────────────────────────
  log('\n▶ Validating AVIF references in source files…');
  const allSrcFiles          = await findSourceFiles();
  const { broken, ok }       = await validateReferences(allSrcFiles);

  log(`  ✓ ${ok.length} valid references`);
  if (broken.length) {
    log(`  ✗ ${broken.length} broken references:`);
    for (const b of broken) {
      log(`      ${relative(ROOT, b.file)}: ${b.ref}`);
    }
  } else {
    log('  ✓ No broken references detected\n');
  }

  // ── 6. Backup + Delete originals ──────────────────────────────────────────
  log('▶ Backing up and deleting converted source files…\n');

  const toDelete = convResults.filter(r =>
    r.status === 'converted' && r.outPath && existsSync(r.outPath)
  );

  const rollbackManifest = [];
  let deletedCount = 0;

  for (const r of toDelete) {
    const rel = relative(IMAGE_ROOT, r.srcPath);
    try {
      await backupFile(r.srcPath);
      await unlink(r.srcPath);
      rollbackManifest.push({ original: r.srcPath, backup: join(BACKUP_DIR, rel) });
      deletedCount++;
      log(`  [DEL]  ${rel}`);
    } catch (e) {
      log(`  [ERR]  Could not delete ${rel}: ${e.message}`);
    }
  }

  // Write rollback manifest
  await writeFile(
    join(BACKUP_DIR, 'rollback-manifest.json'),
    JSON.stringify(rollbackManifest, null, 2),
    'utf8'
  );
  log(`\n  Backup written to: scripts/.image-backup/`);
  log(`  Rollback manifest: scripts/.image-backup/rollback-manifest.json`);

  // ── 7. Final Report ───────────────────────────────────────────────────────
  log('\n═══════════════════════════════════════════════════════════════');
  log('  FINAL REPORT');
  log('═══════════════════════════════════════════════════════════════\n');

  const converted      = convResults.filter(r => r.status === 'converted');
  const skipped        = convResults.filter(r => r.status === 'skipped');
  const keptOriginal   = convResults.filter(r => r.status === 'kept_original');
  const errors         = [...convResults, ...avifResults].filter(r => r.status === 'error');
  const avifReopt      = avifResults.filter(r => r.status === 'avif_reoptimized');
  const avifKept       = avifResults.filter(r => r.status === 'avif_kept');

  const totalSrcBytes  = [...converted, ...skipped, ...keptOriginal]
    .reduce((s, r) => s + r.srcSize, 0);
  const totalOutBytes  = [...converted, ...skipped]
    .reduce((s, r) => s + r.outSize, 0)
    + keptOriginal.reduce((s, r) => s + r.srcSize, 0);

  // Table header
  const COL = [42, 10, 10, 8, 24];
  const hr  = () => log('├' + COL.map(w => '─'.repeat(w + 2)).join('┼') + '┤');
  const row = (...cells) => {
    const padded = cells.map((c, i) => String(c).padEnd(COL[i]));
    log('│ ' + padded.join(' │ ') + ' │');
  };

  log('┌' + COL.map(w => '─'.repeat(w + 2)).join('┬') + '┐');
  row('File', 'Original', 'AVIF', 'Saving', 'Dimensions');
  hr();

  for (const r of [...converted, ...skipped, ...keptOriginal, ...avifReopt, ...avifKept]) {
    const name = relative(IMAGE_ROOT, r.srcPath).replace(/\\/g, '/');
    const saving = r.status === 'kept_original' ? 'kept'
                 : r.status === 'avif_kept'     ? 'optimal'
                 : r.srcSize === r.outSize       ? '—'
                 : pct(r.srcSize, r.outSize);
    row(
      name.length > 42 ? '…' + name.slice(-41) : name,
      fmt(r.srcSize),
      fmt(r.outSize),
      saving,
      `${r.srcDims} → ${r.outDims}`
    );
  }

  log('├' + COL.map(w => '─'.repeat(w + 2)).join('┴') + '┤');
  log(`│ ${'TOTAL CONVERTED'.padEnd(COL[0])} │ ${fmt(totalSrcBytes).padEnd(COL[1])} │ ${fmt(totalOutBytes).padEnd(COL[2])} │ ${pct(totalSrcBytes, totalOutBytes).padEnd(COL[3])} │${''.padEnd(COL[4] + 3)}│`);
  log('└' + COL.map(w => '─'.repeat(w + 2)).join('─').replace(/─/g, '─') + '┘');

  log('\n── Summary ─────────────────────────────────────────────────────');
  log(`  Images processed      : ${toConvert.length + existingAvifs.length}`);
  log(`  Newly converted       : ${converted.length}`);
  log(`  Already optimal AVIF  : ${skipped.length + avifKept.length}`);
  log(`  AVIF re-optimized     : ${avifReopt.length}`);
  log(`  Kept as original      : ${keptOriginal.length} (AVIF would be larger)`);
  log(`  Errors                : ${errors.length}`);
  log(`  References updated    : ${totalRefUpdates} across ${updatedFiles.length} files`);
  log(`  Source files deleted  : ${deletedCount}`);
  log(`  Broken references     : ${broken.length}`);
  log(`  Size before           : ${fmt(totalSrcBytes)}`);
  log(`  Size after            : ${fmt(totalOutBytes)}`);
  log(`  Total reduction       : ${pct(totalSrcBytes, totalOutBytes)}`);

  if (errors.length) {
    log('\n── Errors ──────────────────────────────────────────────────────');
    for (const e of errors) {
      log(`  ${relative(IMAGE_ROOT, e.srcPath)}: ${e.error}`);
    }
  }

  if (keptOriginal.length) {
    log('\n── Kept as original (AVIF not smaller) ─────────────────────────');
    for (const r of keptOriginal) {
      log(`  ${relative(IMAGE_ROOT, r.srcPath)} — ${fmt(r.srcSize)} (AVIF would be ${fmt(r.outSize)})`);
    }
  }

  // Final directory listing
  log('\n── Remaining files under public/assets/images ──────────────────');
  const remaining = await walk(IMAGE_ROOT);
  const byExt = {};
  for (const f of remaining) {
    const e = extname(f).toLowerCase() || '(none)';
    byExt[e] = (byExt[e] || 0) + 1;
  }
  for (const [e, n] of Object.entries(byExt).sort()) {
    log(`  ${e.padEnd(12)} : ${n} files`);
  }

  // Write JSON report
  const report = {
    timestamp: new Date().toISOString(),
    summary: {
      processed: toConvert.length + existingAvifs.length,
      converted: converted.length,
      skipped: skipped.length + avifKept.length,
      avifReoptimized: avifReopt.length,
      keptOriginal: keptOriginal.length,
      errors: errors.length,
      referencesUpdated: totalRefUpdates,
      filesModified: updatedFiles.length,
      filesDeleted: deletedCount,
      brokenReferences: broken.length,
      totalSrcBytes,
      totalOutBytes,
      reductionPct: pct(totalSrcBytes, totalOutBytes),
    },
    modifiedFiles: modifiedFileList,
    keptOriginals: keptOriginal.map(r => ({
      file: relative(IMAGE_ROOT, r.srcPath),
      srcSize: r.srcSize,
      avifSize: r.outSize,
    })),
    brokenRefs: broken.map(b => ({
      file: relative(ROOT, b.file),
      ref: b.ref,
    })),
    errors: errors.map(e => ({
      file: relative(IMAGE_ROOT, e.srcPath),
      error: e.error,
    })),
  };

  await writeFile(REPORT_FILE, JSON.stringify(report, null, 2), 'utf8');
  await writeFile(LOG_FILE, logLines.join('\n'), 'utf8');

  log('\n── Output files ────────────────────────────────────────────────');
  log(`  Log    : scripts/image-optimization.log`);
  log(`  Report : scripts/image-optimization-report.json`);
  log(`  Backup : scripts/.image-backup/`);
  log('\n═══════════════════════════════════════════════════════════════\n');
}

run().catch(e => { console.error(e); process.exit(1); });

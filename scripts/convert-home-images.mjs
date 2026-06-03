import sharp from 'sharp';
import { readdir, stat, unlink } from 'fs/promises';
import { join, extname, basename } from 'path';

const DIR = new URL('../public/assets/images/home/', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');

// Max display dimensions for the home page — resize only if larger
const MAX_W = 1920;
const MAX_H = 1920;

// AVIF encoding settings: quality 72 keeps logos crisp, photos sharp
const AVIF_OPTIONS = { quality: 72, effort: 6 };

const CONVERTIBLE = new Set(['.jpg', '.jpeg', '.png', '.webp']);

function fmt(bytes) {
  return (bytes / 1024).toFixed(1) + ' KB';
}

function pct(before, after) {
  return (((before - after) / before) * 100).toFixed(1) + '%';
}

async function getInfo(filePath) {
  const [fileStat, meta] = await Promise.all([stat(filePath), sharp(filePath).metadata()]);
  return { size: fileStat.size, width: meta.width, height: meta.height };
}

async function convert(srcPath) {
  const ext = extname(srcPath).toLowerCase();
  const base = basename(srcPath, ext);
  const outPath = join(DIR, base + '.avif');

  const src = await getInfo(srcPath);

  // Check if an AVIF already exists from a previous run
  let existingAvifSize = null;
  try {
    existingAvifSize = (await stat(outPath)).size;
  } catch { /* not present */ }

  // Build the pipeline
  let pipeline = sharp(srcPath);

  // Resize only if image exceeds display max
  if (src.width > MAX_W || src.height > MAX_H) {
    pipeline = pipeline.resize(MAX_W, MAX_H, { fit: 'inside', withoutEnlargement: true });
  }

  pipeline = pipeline.avif(AVIF_OPTIONS);

  const outBuffer = await pipeline.toBuffer();

  // Skip if existing AVIF is already smaller or equal
  if (existingAvifSize !== null && existingAvifSize <= outBuffer.length) {
    const existing = await getInfo(outPath);
    return {
      file: basename(srcPath),
      srcSize: src.size,
      outSize: existingAvifSize,
      srcDims: `${src.width}×${src.height}`,
      outDims: `${existing.width}×${existing.height}`,
      skipped: true,
    };
  }

  // Write the new AVIF
  await sharp(outBuffer).toFile(outPath);
  const outMeta = await sharp(outPath).metadata();

  return {
    file: basename(srcPath),
    srcSize: src.size,
    outSize: outBuffer.length,
    srcDims: `${src.width}×${src.height}`,
    outDims: `${outMeta.width}×${outMeta.height}`,
    skipped: false,
    outPath,
    srcPath,
  };
}

async function run() {
  const files = (await readdir(DIR))
    .filter(f => CONVERTIBLE.has(extname(f).toLowerCase()))
    .map(f => join(DIR, f));

  if (!files.length) {
    console.log('No convertible images found.');
    return;
  }

  const results = [];
  for (const f of files) {
    process.stdout.write(`  Converting ${basename(f)} … `);
    try {
      const r = await convert(f);
      results.push(r);
      console.log(r.skipped ? 'skipped (existing AVIF is already optimal)' : 'done');
    } catch (e) {
      console.log(`ERROR: ${e.message}`);
      results.push({ file: basename(f), error: e.message });
    }
  }

  // ── Report ────────────────────────────────────────────────────────────────
  console.log('\n┌─────────────────────────────────────────────────────────────────────────────────────────┐');
  console.log('│                          IMAGE OPTIMISATION REPORT                                     │');
  console.log('├──────────────────────────────────┬───────────┬───────────┬─────────┬──────────────────┤');
  console.log('│ File                             │ Orig size │ AVIF size │ Saving  │ Dimensions       │');
  console.log('├──────────────────────────────────┼───────────┼───────────┼─────────┼──────────────────┤');

  let totalSrc = 0, totalOut = 0;
  for (const r of results) {
    if (r.error) {
      console.log(`│ ${r.file.padEnd(32)} │ ERROR: ${r.error.slice(0, 62).padEnd(72)} │`);
      continue;
    }
    totalSrc += r.srcSize;
    totalOut += r.outSize;
    const name = (r.file).padEnd(32);
    const orig = fmt(r.srcSize).padStart(9);
    const out  = fmt(r.outSize).padStart(9);
    const save = (r.skipped ? '–' : pct(r.srcSize, r.outSize)).padStart(7);
    const dims = `${r.srcDims} → ${r.outDims}`.padEnd(16);
    console.log(`│ ${name} │ ${orig} │ ${out} │ ${save} │ ${dims} │`);
  }

  console.log('├──────────────────────────────────┼───────────┼───────────┼─────────┼──────────────────┤');
  const totOrig = fmt(totalSrc).padStart(9);
  const totOut  = fmt(totalOut).padStart(9);
  const totSave = pct(totalSrc, totalOut).padStart(7);
  console.log(`│ ${'TOTAL'.padEnd(32)} │ ${totOrig} │ ${totOut} │ ${totSave} │${''.padEnd(17)}│`);
  console.log('└──────────────────────────────────┴───────────┴───────────┴─────────┴──────────────────┘');

  // ── Delete source files for successfully converted images ─────────────────
  const converted = results.filter(r => !r.error && !r.skipped && r.srcPath);
  if (converted.length) {
    console.log('\nDeleting source files…');
    for (const r of converted) {
      await unlink(r.srcPath);
      console.log(`  deleted ${r.file}`);
    }
  }

  console.log('\nDone.');
}

run().catch(e => { console.error(e); process.exit(1); });

// Run this once: node scripts/download-images.js
//
// Downloads every CassavaForge image currently referenced from Stitch's
// temporary preview URLs and saves it permanently into /public/images.
// After running this, the site no longer depends on those temporary links.

const fs = require("fs");
const path = require("path");

const map = JSON.parse(
  fs.readFileSync(path.join(__dirname, "image-map.json"), "utf8")
);

const outDir = path.join(__dirname, "..", "public", "images");
fs.mkdirSync(outDir, { recursive: true });

async function downloadOne(filename, url) {
  const dest = path.join(outDir, filename);
  try {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const buffer = Buffer.from(await res.arrayBuffer());
    fs.writeFileSync(dest, buffer);
    console.log(`✓ ${filename} (${(buffer.length / 1024).toFixed(0)} KB)`);
  } catch (err) {
    console.error(`✗ ${filename} — ${err.message}`);
  }
}

async function main() {
  const entries = Object.entries(map);
  console.log(`Downloading ${entries.length} images into public/images ...\n`);
  for (const [filename, url] of entries) {
    await downloadOne(filename, url);
  }
  console.log("\nDone. Every page now points at these local files instead of");
  console.log("Stitch's temporary preview links — nothing left to expire.");
}

main();

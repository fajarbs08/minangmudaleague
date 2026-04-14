import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

const baseUrl = 'http://127.0.0.1:8000';
const outDir = '/home/fools/Documents/Laravel/velok/public/workflow-screens';

function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

async function wait(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function annotate(page, items) {
  const annotations = [];

  for (const item of items) {
    const locator = page.locator(item.selector).first();
    await locator.waitFor({ state: 'visible', timeout: 15000 });
    const box = await locator.boundingBox();
    if (!box) {
      continue;
    }
    annotations.push({
      x: box.x,
      y: box.y,
      width: box.width,
      height: box.height,
      label: item.label,
      tone: item.tone || '#b45309',
    });
  }

  await page.evaluate((marks) => {
    document.querySelectorAll('[data-workflow-overlay-root]').forEach((node) => node.remove());

    const root = document.createElement('div');
    root.setAttribute('data-workflow-overlay-root', 'true');
    root.style.position = 'absolute';
    root.style.left = '0';
    root.style.top = '0';
    root.style.width = Math.max(
      document.documentElement.scrollWidth,
      document.body.scrollWidth
    ) + 'px';
    root.style.height = Math.max(
      document.documentElement.scrollHeight,
      document.body.scrollHeight
    ) + 'px';
    root.style.pointerEvents = 'none';
    root.style.zIndex = '2147483647';
    document.body.appendChild(root);

    marks.forEach((mark, index) => {
      const frame = document.createElement('div');
      frame.style.position = 'absolute';
      frame.style.left = `${mark.x - 8}px`;
      frame.style.top = `${mark.y - 8}px`;
      frame.style.width = `${mark.width + 16}px`;
      frame.style.height = `${mark.height + 16}px`;
      frame.style.border = `4px solid ${mark.tone}`;
      frame.style.borderRadius = '18px';
      frame.style.boxShadow = '0 8px 24px rgba(15, 23, 42, 0.18)';
      frame.style.background = 'rgba(255,255,255,0.04)';

      const pin = document.createElement('div');
      pin.textContent = String(index + 1);
      pin.style.position = 'absolute';
      pin.style.left = `${mark.x - 10}px`;
      pin.style.top = `${mark.y - 10}px`;
      pin.style.width = '22px';
      pin.style.height = '22px';
      pin.style.borderRadius = '999px';
      pin.style.background = mark.tone;
      pin.style.color = '#fff';
      pin.style.font = '700 13px Arial, sans-serif';
      pin.style.lineHeight = '22px';
      pin.style.textAlign = 'center';
      pin.style.boxShadow = '0 6px 18px rgba(15, 23, 42, 0.2)';

      root.appendChild(frame);
      root.appendChild(pin);
    });
  }, annotations);
}

async function clearAnnotations(page) {
  await page.evaluate(() => {
    document.querySelectorAll('[data-workflow-overlay-root]').forEach((node) => node.remove());
  });
}

async function main() {
  ensureDir(outDir);

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 980 }, deviceScaleFactor: 1.5 });

  await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', 'garuda@velok.local');
  await page.fill('input[name="password"]', 'club12345');
  await page.click('button[type="submit"]');
  await page.waitForURL(/dashboard/, { timeout: 20000 });
  await wait(1200);

  await annotate(page, [
    { selector: '.menu-link[href$="/clubs"]', label: 'Buka modul Klub', tone: '#0f766e' },
    { selector: '.menu-link[href$="/officials"]', label: 'Lanjut ke Official', tone: '#2563eb' },
    { selector: '.menu-link[href$="/players"]', label: 'Lanjut ke Pemain', tone: '#7c3aed' },
    { selector: '.menu-link[href$="/lineup-lists"]', label: 'Susun DSP di sini', tone: '#b45309' },
    { selector: 'a[href$="/dashboard/club-workflow-pdf"]', label: 'Buka panduan PDF', tone: '#be123c' },
  ]);
  await page.screenshot({ path: path.join(outDir, 'dashboard-annotated.png') });
  await clearAnnotations(page);

  await page.goto(`${baseUrl}/clubs`, { waitUntil: 'networkidle' });
  await wait(1200);
  await page.click('button:has-text("Tindakan")');
  await page.click('a:has-text("Edit")');
  await page.waitForURL(/\/clubs\/\d+\/edit/, { timeout: 15000 });
  await wait(1000);
  await annotate(page, [
    { selector: 'input[name="name"]', label: 'Isi identitas klub', tone: '#0f766e' },
    { selector: 'input[name="deed_file"]', label: 'Unggah akta klub', tone: '#2563eb' },
    { selector: 'input[name="statement_file"]', label: 'Unggah surat pernyataan', tone: '#7c3aed' },
    { selector: 'button.btn.btn-primary:has-text("Update")', label: 'Simpan perubahan dulu', tone: '#b45309' },
  ]);
  await page.screenshot({ path: path.join(outDir, 'club-edit-annotated.png') });
  await clearAnnotations(page);

  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
  await wait(900);
  await annotate(page, [
    { selector: 'button:has-text("Submit Verifikasi")', label: 'Ajukan verifikasi setelah lengkap', tone: '#be123c' },
  ]);
  await page.screenshot({ path: path.join(outDir, 'submit-annotated.png') });
  await clearAnnotations(page);

  await page.goto(`${baseUrl}/officials/create`, { waitUntil: 'networkidle' });
  await wait(1000);
  await annotate(page, [
    { selector: 'select[name="club_id"]', label: 'Pilih klub', tone: '#0f766e' },
    { selector: 'input[name="name"]', label: 'Isi nama official', tone: '#2563eb' },
    { selector: 'input[name="license_file"]', label: 'Unggah bukti lisensi', tone: '#7c3aed' },
    { selector: 'button.btn.btn-primary:has-text("Simpan")', label: 'Simpan official', tone: '#b45309' },
  ]);
  await page.screenshot({ path: path.join(outDir, 'official-create-annotated.png') });
  await clearAnnotations(page);

  await page.goto(`${baseUrl}/players/create`, { waitUntil: 'networkidle' });
  await wait(1000);
  await annotate(page, [
    { selector: 'select[name="club_id"]', label: 'Pilih klub', tone: '#0f766e' },
    { selector: 'input[name="name"]', label: 'Isi nama pemain', tone: '#2563eb' },
    { selector: 'input[name="birth_certificate_file"]', label: 'Unggah akta kelahiran', tone: '#7c3aed' },
    { selector: '#age-registrations', label: 'Atur kelompok usia dan posisi', tone: '#b45309' },
    { selector: 'button.btn.btn-primary:has-text("Simpan")', label: 'Simpan pemain', tone: '#be123c' },
  ]);
  await page.screenshot({ path: path.join(outDir, 'player-create-annotated.png') });
  await clearAnnotations(page);

  await page.goto(`${baseUrl}/lineup-lists/create`, { waitUntil: 'networkidle' });
  await wait(1000);
  await annotate(page, [
    { selector: 'select[name="club_id"]', label: 'Pilih klub', tone: '#0f766e' },
    { selector: 'select[name="age_group_id"]', label: 'Pilih kelompok usia', tone: '#2563eb' },
    { selector: '[data-lineup-starters]', label: 'Tentukan starter', tone: '#7c3aed' },
    { selector: '[data-lineup-substitutes]', label: 'Tentukan cadangan', tone: '#b45309' },
    { selector: 'button.btn.btn-primary:has-text("Simpan")', label: 'Simpan DSP', tone: '#be123c' },
  ]);
  await page.screenshot({ path: path.join(outDir, 'lineup-create-annotated.png') });
  await clearAnnotations(page);

  await browser.close();
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});

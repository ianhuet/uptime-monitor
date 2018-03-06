
const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();
  await page.goto('http://ianhuet.quickconnect.to', {
    waitUntil: "networkidle0"
  });
  await page.screenshot({path: 'example.png'});
  // await page.$({path: 'example.png'});

  await browser.close();
})()
.catch(err => [err]);

const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();
  await page.goto('http://ianhuet.quickconnect.to', {
    waitUntil: "networkidle0"
  });
  // await page.screenshot({path: 'example.png'});
  
  
  let btnStr = await page.evaluate(() => {
    let html = document.querySelector('#login-btn button').innerHTML;
    return html;
  });
  let result = (btnStr == 'Sign In') ? 'online' : 'offline';
  console.log('Availability: ', result, btnStr);

  await browser.close();
})()
.catch(err => [err]);
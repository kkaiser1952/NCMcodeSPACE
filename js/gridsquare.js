// gridsquare.js
// V2 Updated: 2024-05-22

function gridsquare(lat, lng) {
  const clicklatlng = { y: lat, x: lng };
  const point = true;

  if (point) {
    const longDir = clicklatlng.x < 0 ? "W" : "E";
    const latDir = clicklatlng.y < 0 ? "S" : "N";

    let longDeg, longMin;
    if (clicklatlng.x > 0) {
      longDeg = Math.floor(clicklatlng.x);
      longMin = (clicklatlng.x - longDeg) * 100;
    } else {
      longDeg = Math.ceil(clicklatlng.x);
      longMin = (longDeg - clicklatlng.x) * 100;
    }
    const longMin2 = (longMin * 60) / 100;
    const longSec = Math.round((longMin2 - Math.floor(longMin2)) * 60);

    let latDeg, latMin;
    if (clicklatlng.y > 0) {
      latDeg = Math.floor(clicklatlng.y);
      latMin = (clicklatlng.y - latDeg) * 100;
    } else {
      latDeg = Math.ceil(clicklatlng.y);
      latMin = (latDeg - clicklatlng.y) * 100;
    }
    const latMin2 = (latMin * 60) / 100;
    const latSec = Math.round((latMin2 - Math.floor(latMin2)) * 60);

    let strHtml = "<font face='arial' size='3'>Grid Square Calculator<br />\n";
    strHtml += "Lat : " + Math.round(clicklatlng.y * 10000) / 10000 + " " + latDir;
    strHtml += ` (${latDeg}&deg; ${Math.floor(latMin2)}' ${latSec}'' ${latDir})`;
    strHtml += "<br />\n";
    strHtml += "Long : " + Math.round(clicklatlng.x * 10000) / 10000 + " " + longDir;
    strHtml += ` (${longDeg}&deg; ${Math.floor(longMin2)}' ${longSec}'' ${longDir})`;
    strHtml += "<br />\n";

    const ychr = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    const ynum = "0123456789";
    let yqth;
    const ycalc = [0, 0, 0, 0];
    const yn = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

    ycalc[1] = clicklatlng.x + 180;
    ycalc[2] = clicklatlng.y + 90;
    ycalc[3] = ycalc[1] * 10;
    ycalc[4] = ycalc[2] * 10;

    let y = 0;
    for (let yi = 1; yi <= 4; ++yi) {
      for (let yk = 1; yk <= 3; ++yk) {
        let ydiv, yres, ylp;
        if (yk !== 3) {
          if (yi === 1 || yi === 3) {
            ydiv = yk === 1 ? 20 : 2;
          } else {
            ydiv = yk === 1 ? 10 : 1;
          }
          yres = ycalc[yi] / ydiv;
          ycalc[yi] = yres;
          ylp = ycalc[yi] > 0 ? Math.floor(yres) : Math.ceil(yres);
          ycalc[yi] = (ycalc[yi] - ylp) * ydiv;
        } else {
          if (yi === 1 || yi === 3) {
            ydiv = 12;
          } else {
            ydiv = 24;
          }
          yres = ycalc[yi] * ydiv;
          ycalc[yi] = yres;
          ylp = ycalc[yi] > 0 ? Math.floor(yres) : Math.ceil(yres);
        }
        ++y;
        yn[y] = ylp;
      }
    }

    yqth = ychr.charAt(yn[1]) + ychr.charAt(yn[4]) + ynum.charAt(yn[2]);
    yqth += ynum.charAt(yn[5]) + ychr.charAt(yn[3]) + ychr.charAt(yn[6]);
    yqth += ychr.charAt(yn[7]) + ychr.charAt(yn[8]);

    strHtml += "Grid Square: " + yqth + "</font>";

    // Square limits
    const bottomLeftLong = Math.floor(clicklatlng.x / 0.0833333333) * 0.0833333333;
    const bottomLeftLat = Math.floor(clicklatlng.y / 0.0416666666) * 0.0416666666;

    // Rest of the code for drawing the polygon and displaying the info window...

    return strHtml;
  }
}

console.log(gridsquare(39, -94));
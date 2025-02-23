// gridtokoords.js
// V2 Updated: 2024-05-22

"use strict";

const [MAX_LAT, MIN_LAT] = [90, -90];
const [MAX_LON, MIN_LON] = [180, -180];
const LAT_DISTANCE = Math.abs(MIN_LAT) + MAX_LAT;
const LON_DISTANCE = Math.abs(MIN_LON) + MAX_LON;

function subdivisor() {
  const last = [18, 10, 24, 10, 24, 10];
  let i = 0;
  return function () {
    return last[i++] || last[(i = 2) - 1];
  };
}

function parseDigit(digit) {
  const alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  let value = alphabet.indexOf(digit.toUpperCase());
  if (value === -1) {
    value = parseInt(digit, 10);
  }
  return value;
}

function getGridSquare(gridSquareId) {
  if (gridSquareId.length % 2 !== 0) {
    return [null, null, null, null];
  }

  gridSquareId = gridSquareId.toUpperCase();

  let lat = MIN_LAT;
  let lon = MIN_LON;
  let latDiv = LAT_DISTANCE;
  let lonDiv = LON_DISTANCE;

  const baseCalculator = subdivisor();

  for (let i = 0; i < gridSquareId.length; i += 2) {
    const base = baseCalculator();
    const [lonId, latId] = gridSquareId.substring(i, i + 2);

    latDiv /= base;
    lonDiv /= base;

    lat += parseDigit(latId) * latDiv;
    lon += parseDigit(lonId) * lonDiv;
  }

  return [lat, lon, latDiv, lonDiv];
}

// Example usage:
//console.log(getGridSquare("EM29qe78"));
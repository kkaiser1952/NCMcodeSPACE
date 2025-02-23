// circleKoords.js
// V2 Updated: 2024-05-22

let circfeqcnt = 0; // Sets a variable to count how many times the circleKoords() function is called

function circleKoords(e) {
  circfeqcnt++;
  console.log('circfeqcnt: ' + circfeqcnt);

  const circolor = getCircleColor(circfeqcnt);
  const LatLng = e.latlng;
  const lat = LatLng.lat;
  const lng = LatLng.lng;

  console.log('circolor: ' + circolor + '  ' + lat + ' and ' + lng);

  const r = 1609.34; // in meters = 1 mile, 4,828.03 meters in 3 miles
  const group1 = L.featureGroup(); // Allows us to group the circles for easy removal, but not working

  const circleOptions = {
    color: circolor,
    fillOpacity: 0.005,
    fillColor: '#69e'
  };

  let dbr = 5; // Default for miles between circles for general markers
  let maxdist = 0;
  let numberofrings = 1;
  let distancebetween = 0;
  let Lval = 'miles';
  const marker = lastLayer;

  if (marker.getPopup() && marker.getPopup().getContent().indexOf('OBJ') > -1) {
    console.log('@56 content= ' + marker.getPopup().getContent());

    let markerText = '';
    let whoArray = [];
    let markerName = '';
    let ownerCall = '';

    if (marker.getPopup() && marker.getPopup().getContent().indexOf('Objects') > -1) {
      markerText = marker.getPopup().getContent(); // Everything in the marker
      whoArray = markerText.split('<br>'); // add markerText words to an array
      markerName = whoArray[1]; // Get the callsign (I hope)
      ownerCall = markerName.slice(0, -2); // Deletes the number from the call
      const padCall = ownerCall.concat('PAD');

      console.log('@80 markerText= ' + markerText + ' markerName= ' + markerName + '  ownerCall= ' + ownerCall + '  padCall= ' + padCall + '  LatLng= ' + LatLng);

      console.log('@85 objse ' + LatLng.distanceTo(objse)); // in meters
      console.log('@86 objsw ' + LatLng.distanceTo(objsw));
      console.log('@87 objne ' + LatLng.distanceTo(objne));
      console.log('@88 objnw ' + LatLng.distanceTo(objnw));

      maxdist = Math.max(
        LatLng.distanceTo(objse),
        LatLng.distanceTo(objne),
        LatLng.distanceTo(objnw),
        LatLng.distanceTo(objsw)
      ) / 1609.34;

      console.log('@98 SE= ' + se + ' Object maxdist= ' + maxdist + ' from ' + markerName + ' for ' + window[padCall]);
    }
  } else if (!marker.getPopup() || marker.getPopup().getContent().indexOf('OBJ') === -1) {
    maxdist = Math.max(
      LatLng.distanceTo(se),
      LatLng.distanceTo(ne),
      LatLng.distanceTo(nw),
      LatLng.distanceTo(sw)
    ) / 1609.34;

    console.log('@123 Station maxdist: ' + maxdist + ' miles Lval= ' + Lval);
  }

  if (maxdist < 1) {
    Lval = 'feet';
    const maxfeet = maxdist * 5280;
    if (maxdist > 0 && maxdist <= 0.5) {
      dbr = 0.05;
    } else if (maxdist > 0.5 && maxdist <= 1) {
      dbr = 0.075;
    }
    console.log('@132 maxdist= ' + maxdist + ' Lval= ' + Lval);
  } else {
    if (maxdist > 1 && maxdist <= 2) {
      dbr = 0.75;
    } else if (maxdist > 2 && maxdist <= 10) {
      dbr = 1;
    } else if (maxdist > 10 && maxdist <= 50) {
      dbr = 5;
    } else if (maxdist > 50 && maxdist <= 500) {
      dbr = 25;
    } else if (maxdist > 500 && maxdist <= 750) {
      dbr = 50;
    } else if (maxdist > 750 && maxdist <= 1000) {
      dbr = 75;
    } else if (maxdist > 1000 && maxdist <= 2000) {
      dbr = 300;
    } else if (maxdist > 2000 && maxdist <= 6000) {
      dbr = 500;
    } else {
      dbr = 5;
    }
    console.log('@144 maxdist= ' + maxdist + ' Lval= ' + Lval);
  }

  distancebetween = prompt('Distance to furthest corner is ' + maxdist + " " + Lval + ".\n How many " + Lval + " between circles?", dbr);
  console.log('@151 db: ' + distancebetween);

  maxdist = maxdist / distancebetween;
  console.log('@154 distancebetween= ' + distancebetween + ' maxdist= ' + maxdist);

  numberofrings = prompt(Math.round(maxdist) + " circles will cover all these objects.\n How many circles do you want to see?", Math.round(maxdist));
  console.log('@159 numberofrings = ' + numberofrings + ' round(maxdist): ' + Math.round(maxdist, 2));

  let angle1 = 90; // mileage boxes going East
  let angle2 = 270; // mileage boxes going West
  let angle3 = 0; // degree markers

  for (let i = 0; i < numberofrings; i++) {
    const Cname = L.circle([lat, lng], r * (i + 1) * distancebetween, circleOptions);
    Cname.addTo(group1);
    map.addLayer(group1);

    angle1 += 10;
    angle2 += 10;

    if (i === 0) {
      drawDegreeMarkers(lat, lng, r * (i + 1) * distancebetween, 20);
    } else if (i === 5 || i === 2) {
      drawDegreeMarkers(lat, lng, r * (i + 1) * distancebetween, 10);
    } else if (i === numberofrings - 1) {
      drawDegreeMarkers(lat, lng, r * (i + 1) * distancebetween, 5);
    }

    const p_c1 = L.GeometryUtil.destination(L.latLng([lat, lng]), angle1, r * (i + 1) * distancebetween);
    const p_c2 = L.GeometryUtil.destination(L.latLng([lat, lng]), angle2, r * (i + 1) * distancebetween);
    const inMiles = Math.round(r * (i + 1) * distancebetween / 1609.34) + ' Mi';
    const inFeet = Math.round((r * (i + 1) * distancebetween / 1609.34) * 5280) + ' Ft';
    const inKM = Math.round(r * (i + 1) * distancebetween / 1000) + ' Km';
    const inM = Math.round((r * (i + 1) * distancebetween / 1000) * 1000) + ' M';

    const distanceText = Math.round(r * (i + 1) * distancebetween / 1609.34) < 2 ? inFeet + ' <br> ' + inM : inMiles + ' <br> ' + inKM;
    const icon = L.divIcon({ className: 'dist-marker-' + circolor, html: distanceText, iconSize: [60, null] });

    const marker = L.marker(p_c1, { title: inMiles + 'Miles', icon: icon });
    const marker2 = L.marker(p_c2, { title: inMiles + 'Miles', icon: icon });

    marker.addTo(map);
    marker2.addTo(map);
  }

  function drawDegreeMarkers(lat, lng, radius, step) {
    for (let j = 0; j < 360; j += step) {
      const p_c0 = L.GeometryUtil.destination(L.latLng([lat, lng]), j, radius);
      const icon = L.divIcon({ className: 'deg-marger', html: j, iconSize: [40, null] });
      const marker0 = L.marker(p_c0, { title: 'degrees', icon: icon });
      marker0.addTo(map);
    }
  }

  function getCircleColor(count) {
    switch (count) {
      case 1:
        return 'blue';
      case 2:
        return 'red';
      case 3:
        return 'green';
      case 4:
        return 'purple';
      case 5:
        return 'orange';
      default:
        return 'black';
    }
  }
}
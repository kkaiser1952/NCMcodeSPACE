// cookieManagement.js
// This javascript works with the columnPicker.php 
// 2020-10-15 Modified to include time zone selection
// V2 Updated: 2024-05-22

// These are default values, they get changed in NetManager.js by goLocal() and goUTC()
const tzvalue = new Date().getTimezoneOffset(); //alert(tzvalue);
setCookie('tzdiff', tzvalue, 2, 'SameSite=Strict');  // set a cookie with the local time difference from UTC 
setCookie('tz_domain', 'UTC', 2, 'SameSite=Strict'); // and a cookie with the default domain of the time zone

// These two functions are based on right-clicking either the facility or onsite  
// This function removes the number (33) from the cookie list  
// And then replaces the cookie 
function clearFacilityCookie() {
  const netcall = $("#domain").html().trim();
  let facilityCookie = getCookie('columnChoices_' + netcall.trim());

  if (facilityCookie) {  // It found cookies in storage
    var arrayCookies = facilityCookie.split(',');  // create the array called arrayCookies
  } else {
    facilityCookie = "";
    arrayCookies = facilityCookie.split(',');
  }    

  arrayCookies = arrayCookies.filter((item) => item !== "33");
  setCookie('columnChoices_' + netcall.trim(), arrayCookies, 10);
} // End clearFacilityCookie()

// This function is to add the facility cookie (33) to the net
function showFacilityColumn() {
  const netcall = $("#domain").html().trim();
  let facilityCookie = getCookie('columnChoices_' + netcall.trim());

  // get the cookie for this net
  if (facilityCookie) {  // It found cookies in storage
    var arrayCookies = facilityCookie.split(',');  
  } else {
    facilityCookie = "";
    var arrayCookies = facilityCookie.split(',');
  } // End if    

  arrayCookies.push("33");  
  setCookie('columnChoices_' + netcall.trim(), arrayCookies, 10);
} // End showFacilityColumn()

// End of the facility & On site cookie moves

function setCookie(cname, cvalue, exdays, sameSite) {
  const d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  const expires = "expires=" + d.toUTCString();
  const ss = sameSite ? `SameSite=${sameSite}` : '';
  document.cookie = `${cname}=${cvalue};${expires};${ss};path=/`;
}

function getCookie(cname) {
  const name = cname + "=";
  const decodedCookie = decodeURIComponent(document.cookie);
  const ca = decodedCookie.split(';');
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) === ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) === 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function eraseCookie(cookieName) {
  setCookie(cookieName, "", -1);
}

function getCurrent() {
  const value = getCookie(cookieName).split(',');
  value.forEach(showChecked);
}

function showChecked(sh) {
  $('.' + sh).prop('checked', true);
}

function calculate() {
  const numberOfChecked = $('input:checkbox:checked.intrests').length;
  const numberOfRadio = $('input:radio:checked.intrests').length;
  const totalCheckboxes = $('input:checkbox').length;
  const totalRadioboxes = $('input:radio').length;
  const numberNotChecked = totalCheckboxes - numberOfChecked;
  const numberNotChecked2 = $('input:checkbox:not(":checked")').length;

  const arr = $.map($('input:checkbox:checked'), function (e, i) {
    return +e.value;
  });

  const cc = getCookie(cookieName);
  const breakCC = cc.split(',');

  $.each(breakCC, function (index, value) {
    $('input[name="intrests[]"][value="' + value.toString() + '"]').prop("checked", true);
  });

  const x = arr.join(',');

  $('p').text('The checked values are: ' + arr.join(','));

  return x;
} // End calculate() 

// This function uses the net callsign to either read a cookie if it exists or it reads the NetKind table to get
// the columnViews column which indicates the default additional columns to show
function testCookies(nc) {
  // Initialize the three arrays
  let array1011 = [];
  let arrayCookies = [];
  let arrayBoth = [];

  // This test was added because the MARS groups don't want to see the county and state.
  // Removed 17 & 18 the county and state from the default on 2021-09-14
  const arrayDefault = $("#activity").html().includes("MARS") ? ["1", "2", "3", "4", "6", "7", "12", "13", "14", "50"] : ["1", "2", "3", "4", "6", "7", "9", "12", "13", "14", "18"];

  // This sets us up to add the email and phone columns automatically if it's a meeting or event
  if ($("#activity").html().includes("Meeting")) {
    array1011 = ["10", "11"];
  } else if ($("#activity").html().includes("Weather Net")) {
    array1011 = ["10", "11", "17", "18", "24"];
  } else {
    array1011 = [];
  }

  const arrayFD = $("#activity").html().includes("MARS") ? ["2", "50"] : arrayDefault;

  // This adds the Band column by default if the frequency is set to Multiple Bands
  if ($("#add2pgtitle").html().includes("Multiple Bands") || $("#add2pgtitle").html().includes("80/40 Meters")) {
    array1011 = array1011.concat("23");
  }

  // NC is the netcall, if it doesn't exist, go get it
  const netcall = nc ? nc : $("#domain").html().trim();

  // Go get the cookies as stored in cookies (storage) this overrides what is found above
  let myCookie = getCookie('columnChoices_' + netcall.trim());
  if (myCookie) {
    arrayCookies = myCookie.split(',');
  } else {
    myCookie = "";
    arrayCookies = myCookie.split(',');
  }

  // This hides all the extra columns as preparation for showing only the requested ones below
  $(".c5, .c8, .c9, .c10, .c11, .c15, .c16, .c17, .c18, .c59, .c20, .c21, .c22, .c23, .c24, .c30, .c31, .c32, .c33, .c34, .c35").hide();
  $(".c25, .c26, .c27, .c28, .c29").hide(); // Admin Level
  $(".c50, .c51").hide(); // Custom Level

  arrayBoth = [...array1011, ...arrayCookies, ...arrayFD];

  // This called function shows all the required columns
  arrayBoth.forEach(showCol);
} // End testCookies()

// The showCol function is called from the popup columnPicker.php
function showCol(sh) {
  switch (sh) {
    case '5':
      $(".c5").show(); // tt No.
      break;
    case '8':
      $(".c8").show(); // Last Name
      break;
    case '9':
      $(".c9").show(); // Tactical
      break;
    case '10':
      $(".c10").show(); // Phone Number
      break;
    case '11':
      $(".c11").show(); // eMail Address
      break;
    case '15':
      $(".c15").show(); // Credentials
      break;
    case '16':
      $(".c16").show(); // Time On Duty
      break;
    case '17':
      $(".c17").show(); // County
      break;
    case '18':
      $(".c18").show(); // State
      break;
    case '19':
      $(".c59").show(); // District 
      break;
    case '20':
      $(".c20").show(); // Grid
      break;
    case '21':
      $(".c21").show(); // Latitude
      break;
    case '22':
      $(".c22").show(); // Longitude
      break;
    case '23':
      $(".c23").show(); // Band
      break;
    case '24':
      $(".c24").show(); // W3W
      break;
    case '30':
      $(".c30").show(); // team
      break;
    case '31':
      $(".c31").show(); // aprs_call
      break;
    case '32':
      $(".c32").show(); // Country
      break;
    case '33':
      $(".c33").show(); // facility
      break;
    case '34':
      $(".c34").show(); // onSite    
      break;
    case '35':
      $(".c35").show(); // City     
      break;
    case '50':
      $(".c50").show(); // Cat (Custom)
      break;
    case '51':
      $(".c51").show(); // Section (Custom)
      break;
    case '25':
      $(".c25").show(); // recordID
      break;
    case '26':
      $(".c26").show(); // ID
      break;
    case '27':
      $(".c27").show(); // status
      break;
    case '28':
      $(".c28").show(); // home
      break;
    case '29':
      $(".c29").show(); // ipaddress
      break;
  }
} // End of showCol function

console.log('cookieManagement.js has finished executing');
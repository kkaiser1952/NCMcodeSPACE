/* W3Data ver 1.31 by W3Schools.com */
// w3data.js
// V2 Updated: 2024-05-22

const w3DataObject = {};

function w3DisplayData(id, data) {
  let htmlObj, htmlTemplate, html, arr = [], rowClone, repeatObj;

  htmlObj = document.getElementById(id);
  htmlTemplate = w3InitTemplate(id, htmlObj);
  html = htmlTemplate.cloneNode(true);
  arr = w3GetElementsByAttribute(html, "w3-repeat");

  for (let j = arr.length - 1; j >= 0; j--) {
    const [repeatX, , repeat] = arr[j].getAttribute("w3-repeat").split(" ");
    arr[j].removeAttribute("w3-repeat");
    repeatObj = data[repeat];

    if (Array.isArray(repeatObj)) {
      for (let i = 0; i < repeatObj.length; i++) {
        rowClone = arr[j];
        rowClone = w3NeedleInHaystack(rowClone, "element", repeatX, repeatObj[i]);

        const attributes = rowClone.attributes;
        for (let ii = 0; ii < attributes.length; ii++) {
          attributes[ii].value = w3NeedleInHaystack(attributes[ii], "attribute", repeatX, repeatObj[i]).value;
        }

        i === repeatObj.length - 1
          ? arr[j].parentNode.replaceChild(rowClone, arr[j])
          : arr[j].parentNode.insertBefore(rowClone, arr[j]);
      }
    } else {
      console.log(`w3-repeat must be an array. ${repeat} is not an array.`);
    }
  }

  html = w3NeedleInHaystack(html, "element");
  htmlObj.parentNode.replaceChild(html, htmlObj);
}

function w3InitTemplate(id, obj) {
  if (w3DataObject.hasOwnProperty(id)) {
    return w3DataObject[id];
  }

  const template = obj.cloneNode(true);
  w3DataObject[id] = template;
  return template;
}

function w3GetElementsByAttribute(x, attribute) {
  const arr = [];
  const elements = x.getElementsByTagName("*");
  const attributeUpper = attribute.toUpperCase();

  for (let i = 0; i < elements.length; i++) {
    if (elements[i].getAttribute(attributeUpper) !== null) {
      arr.push(elements[i]);
    }
  }

  return arr;
}

function w3NeedleInHaystack(elmnt, type, repeatX, x) {
  let value, rowClone, pos1, haystack, pos2, needle, needleToReplace;

  rowClone = elmnt.cloneNode(true);
  pos1 = 0;

  while (pos1 > -1) {
    haystack = type === "attribute" ? rowClone.value : rowClone.innerHTML;
    pos1 = haystack.indexOf("{{", pos1);
    if (pos1 === -1) break;

    pos2 = haystack.indexOf("}}", pos1 + 1);
    needleToReplace = haystack.substring(pos1 + 2, pos2);
    needle = needleToReplace.split("||");
    value = undefined;

    for (let i = 0; i < needle.length; i++) {
      needle[i] = needle[i].trim();

      if (x && x.hasOwnProperty(needle[i])) {
        value = x[needle[i]];
      } else if (data && data.hasOwnProperty(needle[i])) {
        value = data[needle[i]];
      } else {
        const [key, prop] = needle[i].split(".");
        if (key === repeatX && x && x.hasOwnProperty(prop)) {
          value = x[prop];
        } else if (needle[i] === repeatX) {
          value = x;
        } else if (needle[i].startsWith('"') || needle[i].startsWith("'")) {
          value = needle[i].slice(1, -1);
        }
      }

      if (value !== undefined) break;
    }

    if (value !== undefined) {
      const r = `{{${needleToReplace}}}`;
      if (type === "attribute") {
        rowClone.value = rowClone.value.replace(r, value);
      } else {
        w3ReplaceHTML(rowClone, r, value);
      }
    }

    pos1++;
  }

  return rowClone;
}

function w3ReplaceHTML(element, target, result) {
  if (element.hasAttributes()) {
    const attributes = element.attributes;
    for (let i = 0; i < attributes.length; i++) {
      if (attributes[i].value.includes(target)) {
        attributes[i].value = attributes[i].value.replace(target, result);
      }
    }
  }

  const children = element.getElementsByTagName("*");
  for (let i = 0; i < children.length; i++) {
    if (children[i].innerHTML.includes(target)) {
      children[i].innerHTML = children[i].innerHTML.replace(target, result);
    }
  }

  element.innerHTML = element.innerHTML.replace(target, result);
}

async function w3IncludeHTML() {
  const elements = document.getElementsByTagName("*");

  for (let i = 0; i < elements.length; i++) {
    const file = elements[i].getAttribute("w3-include-html");

    if (file) {
      try {
        const response = await fetch(file);
        if (response.ok) {
          elements[i].innerHTML = await response.text();
          elements[i].removeAttribute("w3-include-html");
          w3IncludeHTML();
        } else {
          console.error(`Failed to include HTML file: ${file}`);
        }
      } catch (error) {
        console.error(`Error including HTML file: ${file}`, error);
      }
      return;
    }
  }
}

async function w3Http(target, readyfunc, xml, method = "GET") {
  try {
    const response = await fetch(target, {
      method,
      body: xml,
    });

    if (response.ok) {
      if (readyfunc) readyfunc(response);
    } else {
      console.error(`HTTP request failed: ${response.status}`);
    }
  } catch (error) {
    console.error("Error making HTTP request:", error);
  }
}
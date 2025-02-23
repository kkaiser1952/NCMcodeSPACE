// sortTable.js
// V2 Updated: 2024-05-14 the innerSortFunction(e)
const sorttable = {
  init() {
    if (arguments.callee.done) return;
    arguments.callee.done = true;

    if (!document.createElement || !document.getElementsByTagName) return;

    sorttable.DATE_RE = /^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/;

    document.querySelectorAll('table.sortable').forEach((table) => {
      sorttable.makeSortable(table);
    });
  },

  makeSortable(table) {
    if (table.getElementsByTagName('thead').length === 0) {
      const thead = document.createElement('thead');
      thead.appendChild(table.rows[0]);
      table.insertBefore(thead, table.firstChild);
    }

    if (table.tHead === null) table.tHead = table.getElementsByTagName('thead')[0];

    if (table.tHead.rows.length !== 1) return;

    const sortbottomrows = [];
    for (let i = 0; i < table.rows.length; i++) {
      if (table.rows[i].className.search(/\bsortbottom\b/) !== -1) {
        sortbottomrows.push(table.rows[i]);
      }
    }

    if (sortbottomrows.length > 0) {
      if (table.tFoot === null) {
        const tfoot = document.createElement('tfoot');
        table.appendChild(tfoot);
      }
      sortbottomrows.forEach((row) => {
        table.tFoot.appendChild(row);
      });
    }

    const headrow = table.tHead.rows[0].cells;
    for (let i = 0; i < headrow.length; i++) {
      if (!headrow[i].className.match(/\bsorttable_nosort\b/)) {
        const mtch = headrow[i].className.match(/\bsorttable_([a-z0-9]+)\b/);
        const override = mtch ? mtch[1] : '';
        if (mtch && typeof sorttable[`sort_${override}`] === 'function') {
          headrow[i].sorttable_sortfunction = sorttable[`sort_${override}`];
        } else {
          headrow[i].sorttable_sortfunction = sorttable.guessType(table, i);
        }
        headrow[i].sorttable_columnindex = i;
        headrow[i].sorttable_tbody = table.tBodies[0];
        headrow[i].addEventListener('click', sorttable.innerSortFunction);
      }
    }
  },

  guessType(table, column) {
    let sortfn = sorttable.sort_alpha;
    for (let i = 0; i < table.tBodies[0].rows.length; i++) {
      const text = sorttable.getInnerText(table.tBodies[0].rows[i].cells[column]);
      if (text !== '') {
        if (text.match(/^-?[£$¤]?[\d,.]+%?$/)) {
          return sorttable.sort_numeric;
        }
        const possdate = text.match(sorttable.DATE_RE);
        if (possdate) {
          const first = parseInt(possdate[1]);
          const second = parseInt(possdate[2]);
          if (first > 12) {
            return sorttable.sort_ddmm;
          } else if (second > 12) {
            return sorttable.sort_mmdd;
          } else {
            sortfn = sorttable.sort_ddmm;
          }
        }
      }
    }
    return sortfn;
  },

  getInnerText(node) {
    if (!node) return '';

    const hasInputs = (typeof node.getElementsByTagName === 'function') &&
      node.getElementsByTagName('input').length > 0;

    if (node.getAttribute('sorttable_customkey') !== null) {
      return node.getAttribute('sorttable_customkey').trim();
    } else if (typeof node.textContent !== 'undefined' && !hasInputs) {
      return node.textContent.trim();
    } else if (typeof node.innerText !== 'undefined' && !hasInputs) {
      return node.innerText.trim();
    } else if (typeof node.text !== 'undefined' && !hasInputs) {
      return node.text.trim();
    } else {
      switch (node.nodeType) {
        case 3:
          if (node.nodeName.toLowerCase() === 'input') {
            return node.value.trim();
          }
        case 4:
          return node.nodeValue.trim();
        case 1:
        case 11:
          let innerText = '';
          for (let i = 0; i < node.childNodes.length; i++) {
            innerText += sorttable.getInnerText(node.childNodes[i]);
          }
          return innerText.trim();
        default:
          return '';
      }
    }
  },

  reverse(tbody) {
    const newrows = Array.from(tbody.rows);
    newrows.reverse().forEach((row) => {
      tbody.appendChild(row);
    });
  },

  sort_numeric(a, b) {
    const aa = parseFloat(a[0].replace(/[^0-9.-]/g, ''));
    const bb = parseFloat(b[0].replace(/[^0-9.-]/g, ''));
    return aa - bb;
  },

  sort_alpha(a, b) {
    if (a[0] === b[0]) return 0;
    if (a[0] < b[0]) return -1;
    return 1;
  },

  sort_ddmm(a, b) {
    const mtch = a[0].match(sorttable.DATE_RE);
    const y = mtch[3];
    const m = mtch[2].padStart(2, '0');
    const d = mtch[1].padStart(2, '0');
    const dt1 = y + m + d;
    const mtch2 = b[0].match(sorttable.DATE_RE);
    const y2 = mtch2[3];
    const m2 = mtch2[2].padStart(2, '0');
    const d2 = mtch2[1].padStart(2, '0');
    const dt2 = y2 + m2 + d2;
    return dt1.localeCompare(dt2);
  },

  sort_mmdd(a, b) {
    const mtch = a[0].match(sorttable.DATE_RE);
    const y = mtch[3];
    const d = mtch[2].padStart(2, '0');
    const m = mtch[1].padStart(2, '0');
    const dt1 = y + m + d;
    const mtch2 = b[0].match(sorttable.DATE_RE);
    const y2 = mtch2[3];
    const d2 = mtch2[2].padStart(2, '0');
    const m2 = mtch2[1].padStart(2, '0');
    const dt2 = y2 + m2 + d2;
    return dt1.localeCompare(dt2);
  },

  shaker_sort(list, comp_func) {
    let b = 0;
    let t = list.length - 1;
    let swap = true;

    while (swap) {
      swap = false;
      for (let i = b; i < t; ++i) {
        if (comp_func(list[i], list[i + 1]) > 0) {
          [list[i], list[i + 1]] = [list[i + 1], list[i]];
          swap = true;
        }
      }
      t--;

      if (!swap) break;

      for (let i = t; i > b; --i) {
        if (comp_func(list[i], list[i - 1]) < 0) {
          [list[i], list[i - 1]] = [list[i - 1], list[i]];
          swap = true;
        }
      }
      b++;
    }
  },

  innerSortFunction(e) {
  const theadrow = this.parentNode;
  document.querySelectorAll('th').forEach((th) => {
    if (th.nodeType === 1) {
      th.className = th.className.replace('sorttable_sorted_reverse', '');
      th.className = th.className.replace('sorttable_sorted', '');
    }
  });
  document.getElementById('sorttable_sortfwdind')?.parentNode.removeChild(document.getElementById('sorttable_sortfwdind'));
  document.getElementById('sorttable_sortrevind')?.parentNode.removeChild(document.getElementById('sorttable_sortrevind'));

  if (this.className.search(/\bsorttable_sorted\b/) !== -1) {
    sorttable.reverse(this.sorttable_tbody);
    this.className = this.className.replace('sorttable_sorted', 'sorttable_sorted_reverse');
    this.removeChild(document.getElementById('sorttable_sortfwdind'));
    const sortrevind = document.createElement('span');
    sortrevind.id = 'sorttable_sortrevind';
    const arrowCharReverse = '&#x25B4;';
    sortrevind.innerHTML = '&nbsp;' + arrowCharReverse;
    this.appendChild(sortrevind);
  } else if (this.className.search(/\bsorttable_sorted_reverse\b/) !== -1) {
    sorttable.reverse(this.sorttable_tbody);
    this.className = this.className.replace('sorttable_sorted_reverse', 'sorttable_sorted');
    this.removeChild(document.getElementById('sorttable_sortrevind'));
    const sortfwdind = document.createElement('span');
    sortfwdind.id = 'sorttable_sortfwdind';
    const arrowChar = '&#x25BE;';
    sortfwdind.innerHTML = '&nbsp;' + arrowChar;
    this.appendChild(sortfwdind);
  } else {
    this.className += ' sorttable_sorted';
    const sortfwdind = document.createElement('span');
    sortfwdind.id = 'sorttable_sortfwdind';
    const arrowChar = '&#x25BE;';
    sortfwdind.innerHTML = '&nbsp;' + arrowChar;
    this.appendChild(sortfwdind);

    try {
      const rowsArray = Array.from(this.sorttable_tbody.rows);
      const col = this.sorttable_columnindex;
      rowsArray.sort((a, b) => {
        const aText = sorttable.getInnerText(a.cells[col]);
        const bText = sorttable.getInnerText(b.cells[col]);
        return this.sorttable_sortfunction(aText, bText);
      });

      const tb = this.sorttable_tbody;
      rowsArray.forEach((row) => {
        tb.appendChild(row);
      });
    } catch (error) {
      console.error('Error occurred while sorting:', error);
      // Handle the error or display an appropriate message to the user
    }
  }
}, // End innerSortFunction
};

// Modernized event handling and initialization
document.addEventListener('DOMContentLoaded', sorttable.init);
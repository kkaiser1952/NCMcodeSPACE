// getNetID.js
// V2 Updated: 2024-05-22

(function () {
  const v = "1.3.2";

  function initMyBookmarklet() {
    window.myBookmarklet = function () {
      function getSelText() {
        let s = "";
        if (window.getSelection) {
          s = window.getSelection().toString();
        } else if (document.getSelection) {
          s = document.getSelection().toString();
        } else if (document.selection) {
          s = document.selection.createRange().text;
        }
        return s;
      }

      if ($("#wikiframe").length === 0) {
        let s = getSelText();
        if (!s) {
          s = prompt("Forget something?");
        }

        if (s) {
          $("body").append(`
            <div id="wikiframe">
              <div id="wikiframe_veil" style="display: none;">
                <p>Loading...</p>
              </div>
              <iframe src="https://en.wikipedia.org/w/index.php?&search=${encodeURIComponent(s)}" onload="$('#wikiframe iframe').slideDown(500);">Enable iFrames.</iframe>
              <style>
                #wikiframe_veil {
                  position: fixed;
                  width: 100%;
                  height: 100%;
                  top: 0;
                  left: 0;
                  background-color: rgba(255, 255, 255, 0.25);
                  cursor: pointer;
                  z-index: 900;
                }
                #wikiframe_veil p {
                  color: black;
                  font: normal normal bold 20px/20px Helvetica, sans-serif;
                  position: absolute;
                  top: 50%;
                  left: 50%;
                  width: 10em;
                  margin: -10px auto 0 -5em;
                  text-align: center;
                }
                #wikiframe iframe {
                  display: none;
                  position: fixed;
                  top: 10%;
                  left: 10%;
                  width: 80%;
                  height: 80%;
                  z-index: 999;
                  border: 10px solid rgba(0, 0, 0, 0.5);
                  margin: -5px 0 0 -5px;
                }
              </style>
            </div>
          `);
          $("#wikiframe_veil").fadeIn(750);
        }
      } else {
        $("#wikiframe_veil").fadeOut(750);
        $("#wikiframe iframe").slideUp(500, function () {
          $("#wikiframe").remove();
        });
      }

      $("#wikiframe_veil").on("click", function () {
        $("#wikiframe_veil").fadeOut(750);
        $("#wikiframe iframe").slideUp(500, function () {
          $("#wikiframe").remove();
        });
      });
    };
  }

  if (window.jQuery === undefined || window.jQuery.fn.jquery < v) {
    const script = document.createElement("script");
    script.src = `https://ajax.googleapis.com/ajax/libs/jquery/${v}/jquery.min.js`;
    script.onload = script.onreadystatechange = function () {
      if (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") {
        initMyBookmarklet();
      }
    };
    document.getElementsByTagName("head")[0].appendChild(script);
  } else {
    initMyBookmarklet();
  }
})();
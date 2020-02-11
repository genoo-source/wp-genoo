/**
 * Genoo Admin
 *
 * @version 1.0.5
 * @author Genoo LLC
 */

/*********************************************************************/

/**
 * Tools
 * @type {*|Object}
 */

var Api = Api || {};

/**
 * Set Function
 *
 * @param what
 * @param value
 * @param action
 */
Api.set = function(what, value, action) {
  // Admin ajax to set API
  return jQuery.ajax({
    type: "POST",
    url: GenooVars.AJAX,
    data: {
      _ajax_nonce: GenooVars.AJAX_NONCE,
      action: action,
      option: what, // your option variable
      value: value // your new value variable
    },
    dataType: "json"
  });
};

/**
 * Timer
 */
var Timer;

/**
 * Check URL
 * @param element
 */
Api.checkUrl = function(element) {
  var value = element.value;
  Api.typeWatch(function() {
    jQuery.ajax({
      type: "POST",
      url: GenooVars.AJAX,
      data: {
        action: "check_url",
        _ajax_nonce: GenooVars.AJAX_NONCE,
        url: value
      },
      success: function(result) {
        if (result == "TRUE") {
          alert("Page or Post with this URL already exists.");
        }
      }
    });
  }, 100);
};

/**
 * Typewatch
 * @returns {Function}
 */
Api.typeWatch = (function() {
  var timer = 0;
  return function(callback, ms) {
    clearTimeout(timer);
    timer = setTimeout(callback, ms);
  };
})();

/**
 * Prologned List to swap
 *
 * @param that
 * @param event
 * @param id
 */
Api.prolognedList = function(that, event, id) {
  if (event) {
    event.returnValue = null;
    if (event.preventDefault) event.preventDefault();
  }
  var show = that.getElementsByTagName("span")[0];
  if (show.innerHTML == "Show") {
    show.innerHTML = "Hide";
  } else {
    show.innerHTML = "Show";
  }
  jQuery("#" + id + " .next").toggleClass("hidden");
};

/**
 * Resize iframe to size
 * @param height
 */
Api.resizeIframe = function(height) {
  // Get iframe
  var Iframe = jQuery("#genooIframe");
  if (height) {
    //Iframe.height(height + 100);
    Iframe.height(height + 600);
  } else {
    Iframe.height(
      Iframe.contents()
        .find("html")
        .height()
    );
  }
};

/**
 * Fadein loader
 * @param message
 */
Api.loader = function(message) {
  jQuery("body").append(
    '<div class="WPMKTENGINELoading" id="WPMKTENGINELoading" style="display: none"><div class="WPMKTENGINELoader"><img src="' +
      GenooVars.GenooPluginUrl +
      'logo.png"><div class="WPMKTENGINELoaderImage"></div><strong id="WPMKTENGINELoaderMessage">' +
      message +
      "</strong></div></div>"
  );
  jQuery("#WPMKTENGINELoading").fadeIn("slow");
};

/**
 * Enable logging
 * @type {boolean}
 */
Api.logging = false;

/**
 * Log data
 * @param log
 */
Api.logMessage = function(log) {
  if (Api.logging) {
    console.log(log);
  }
};

/**
 * Loader
 */
Api.loader.load = function() {
  // let's do this
  if (Api.data.key == true && Api.data.track == true) {
    // We have all data, let's do this
    Api.loader("Please wait while we set up your installation.");
    Api.logMessage("Loader loaded, API key and Tracking code recieved");
    // Set values
    // Magic cycle
    jQuery
      .when(Api.set(Api.data.keyName, Api.data.keyData, "update_option_api"))
      .done(function(a1) {
        // Log and change message
        Api.logMessage("APi key updated via AJAX call, changing message..");
        jQuery("#WPMKTENGINELoaderMessage").text("Setting up tracking code.");
        jQuery
          .when(
            Api.set(Api.data.trackName, Api.data.trackData, "update_option_api")
          )
          .done(function(a2) {
            // Set up leads
            Api.logMessage(
              "Tracking updated via AJAX call, changing message..."
            );
            jQuery("#WPMKTENGINELoaderMessage").text("Setting up leads.");
            jQuery
              .when(Api.set("update", "true", "update_leads"))
              .done(function(a3) {
                // Donito done
                Api.logMessage("Finished setup, changing message...");
                jQuery("#WPMKTENGINELoaderMessage").text(
                  "Finished setting up your WordPress installation."
                );
                setTimeout(function() {
                  window.location = window.location.href;
                }, 2000);
              });
          });
      });
  }
};

/**
 * Fadeout
 */
Api.loader.close = function() {
  jQuery("#WPMKTENGINELoading").fadeOut("slow");
};

/**
 * Refresh Forms
 */
Api.refreshForms = function() {
  Api.loader("Setting up your lead capture forms.");
  jQuery
    .when(Api.set("refreshForms", "true", "refresh_forms"))
    .done(function(a1) {
      Api.loader.close();
    });
};

Api.refreshSurveys = function() {
  Api.loader("Setting up your surveys.");
  jQuery
    .when(Api.set("refreshSurveys", "true", "refresh_surveys"))
    .done(function(a1) {
      Api.loader.close();
    });
};

/**
 * Listener
 *
 * @param event
 * @returns {*}
 */
Api.listener = function(event) {
  if (window.location.href.indexOf("&gn-thm=") > -1) {
    return;
  }
  if (window.location.href.indexOf("wp-admin/customize.php") > -1) {
    return;
  }
  // Event origin
  if (event.origin !== "https:" + GenooVars.DOMAIN) {
    console.log("Wrong origin: " + event.origin);
    return;
  }
  // Event Data
  if ("name" in event.data && "value" in event.data) {
    // Switch statement
    switch (event.data.name) {
      case "apikey":
      case "trackingkey":
        // Set values and open loaded
        Api.logMessage(
          "Api.set('" +
            event.data.name +
            "', '" +
            event.data.value +
            "', 'update_option_api')"
        );
        if (event.data.name == "apikey") {
          Api.data.key = true;
          Api.data.keyName = event.data.name;
          Api.data.keyData = event.data.value;
        } else {
          Api.data.track = true;
          Api.data.trackName = event.data.name;
          Api.data.trackData = event.data.value;
        }
        // Pass loader
        return Api.loader.load();
        break;
      case "resizeiframe":
        console.log("setting" + event.data.name);
        Api.resizeIframe(event.data.value);
        break;
      case "formsRefresh":
        console.log("setting" + event.data.name);
        Api.refreshForms();
        break;
      case "surveysRefresh":
        console.log("setting" + event.data.name);
        Api.refreshSurveys();
        break;
      case "changeAttribute":
        console.log("changing attribute" + event.data);
        // Element data
        var elementId = event.data.id;
        var elementAtt = event.data.attribute;
        var elementVal = event.data.value;
        var element = document.getElementById(elementId);
        if (element != "undefined" && element != null) {
          // Exists, changing
          element.setAttribute(elementAtt, elementVal);
        } else {
          console.log("Element doesnt exist");
        }
        break;
    }
  } else {
    console.log("This is interesting.");
  }
};

/**
 * Attach Javascript API listener
 */
Api.attach = function() {
  /**
   * Prepp data
   * @type {{}}
   */
  Api.data = {};
  Api.data.key = false;
  Api.data.keyName = null;
  Api.data.keyData = null;
  Api.data.track = false;
  Api.data.trackName = null;
  Api.data.trackData = null;

  /**
   * Add listeners
   */
  if (window.addEventListener) {
    addEventListener("message", Api.listener, false);
  } else {
    attachEvent("onmessage", Api.listener);
  }
};

Api.refresh = function(where) {};

/**
 * Tools
 * @type {*|Object}
 */

var Tool = Tool || {};

/**
 * Prompt that goes to URL
 *
 * @param question
 * @param url
 */
Tool.promptGo = function(question, url, value) {
  var name = prompt(question, value || "");
  if (name) {
    window.location = url + encodeURIComponent(name);
  }
};

/**
 * Prompt that goes to URL
 *
 * @param question
 * @param url
 */
Tool.promptToRename = function(question, url, value) {
  // Get values
  var values = value ? value.split(" / ") : [];
  // Get last one
  var valuesName = values.pop();
  var name = prompt(question, valuesName || "");
  if (name) {
    values.push(name);
    var finalName = values.join(" / ");
    window.location = url + encodeURIComponent(finalName);
  }
};

/**
 * Prompt before going to url
 *
 * @param event
 * @param that
 * @param message
 * @param url
 * @returns {boolean}
 */
Tool.promptBeforeGo = function(event, that, message, url) {
  if (event) {
    event.returnValue = null;
    if (event.preventDefault) event.preventDefault();
  }
  if (that) {
    if (that.hasAttribute("href")) {
      url = that.getAttribute("href");
    }
  }
  var confirmation = confirm(message);
  if (!confirmation) {
    return false;
  } else {
    window.location = url;
  }
};

/**
 * Has class
 *
 * @param el
 * @param className
 * @return {Boolean}
 */

Tool.hasClass = function(el, className) {
  if (el.classList) return el.classList.contains(className);
  else
    return new RegExp("(^| )" + className + "( |$)", "gi").test(el.className);
};

/**
 * Add class
 *
 * @param el
 * @param className
 */

Tool.addClass = function(el, className) {
  if (el.classList) el.classList.add(className);
  else el.className += " " + className;
};

/**
 * Remove class
 *
 * @param el
 * @param className
 */

Tool.removeClass = function(el, className) {
  if (el.classList) el.classList.remove(className);
  else
    el.className = el.className.replace(
      new RegExp("(^|\\b)" + className.split(" ").join("|") + "(\\b|$)", "gi"),
      " "
    );
};

/**
 * Switch class
 *
 * @param element
 * @param className
 */

Tool.switchClass = function(element, className) {
  if (Tool.hasClass(element, className)) {
    Tool.removeClass(element, className);
  } else {
    Tool.addClass(element, className);
  }
};

/**
 * Switch tab
 *
 * @param el
 * @param id
 */

Tool.switchTab = function(el, id) {
  var selected = el.options[el.selectedIndex].value;
  var tabHtml = document.getElementById(id + "html");
  var tabImg = document.getElementById(id + "img");
  var tabCurrent = document.getElementById(id + selected);
  Tool.switchClass(tabHtml, "hidden");
  Tool.switchClass(tabImg, "hidden");
};

/**
 * Version compare (js copy of PHP code)
 *
 * @param v1
 * @param v2
 * @param operator
 * @returns {*}
 */

Tool.versionCompare = function(v1, v2, operator) {
  //       discuss at: http://phpjs.org/functions/version_compare/
  //      original by: Philippe Jausions (http://pear.php.net/user/jausions)
  //      original by: Aidan Lister (http://aidanlister.com/)
  // reimplemented by: Kankrelune (http://www.webfaktory.info/)
  //      improved by: Brett Zamir (http://brett-zamir.me)
  //      improved by: Scott Baker
  //      improved by: Theriault
  //        example 1: version_compare('8.2.5rc', '8.2.5a');
  //        returns 1: 1
  //        example 2: version_compare('8.2.50', '8.2.52', '<');
  //        returns 2: true
  //        example 3: version_compare('5.3.0-dev', '5.3.0');
  //        returns 3: -1
  //        example 4: version_compare('4.1.0.52','4.01.0.51');
  //        returns 4: 1

  this.compare = this.compare || {};
  this.compare.ENV = this.compare.ENV || {};
  // END REDUNDANT
  // Important: compare must be initialized at 0.
  var i = 0,
    x = 0,
    compare = 0,
    // vm maps textual PHP versions to negatives so they're less than 0.
    // PHP currently defines these as CASE-SENSITIVE. It is important to
    // leave these as negatives so that they can come before numerical versions
    // and as if no letters were there to begin with.
    // (1alpha is < 1 and < 1.1 but > 1dev1)
    // If a non-numerical value can't be mapped to this table, it receives
    // -7 as its value.
    vm = {
      dev: -6,
      alpha: -5,
      a: -5,
      beta: -4,
      b: -4,
      RC: -3,
      rc: -3,
      "#": -2,
      p: 1,
      pl: 1
    },
    // This function will be called to prepare each version argument.
    // It replaces every _, -, and + with a dot.
    // It surrounds any nonsequence of numbers/dots with dots.
    // It replaces sequences of dots with a single dot.
    //    version_compare('4..0', '4.0') == 0
    // Important: A string of 0 length needs to be converted into a value
    // even less than an unexisting value in vm (-7), hence [-8].
    // It's also important to not strip spaces because of this.
    //   version_compare('', ' ') == 1
    prepVersion = function(v) {
      v = ("" + v).replace(/[_\-+]/g, ".");
      v = v.replace(/([^.\d]+)/g, ".$1.").replace(/\.{2,}/g, ".");
      return !v.length ? [-8] : v.split(".");
    };
  // This converts a version component to a number.
  // Empty component becomes 0.
  // Non-numerical component becomes a negative number.
  // Numerical component becomes itself as an integer.
  numVersion = function(v) {
    return !v ? 0 : isNaN(v) ? vm[v] || -7 : parseInt(v, 10);
  };
  v1 = prepVersion(v1);
  v2 = prepVersion(v2);
  x = Math.max(v1.length, v2.length);
  for (i = 0; i < x; i++) {
    if (v1[i] == v2[i]) {
      continue;
    }
    v1[i] = numVersion(v1[i]);
    v2[i] = numVersion(v2[i]);
    if (v1[i] < v2[i]) {
      compare = -1;
      break;
    } else if (v1[i] > v2[i]) {
      compare = 1;
      break;
    }
  }
  if (!operator) {
    return compare;
  }

  // Important: operator is CASE-SENSITIVE.
  // "No operator" seems to be treated as "<."
  // Any other values seem to make the function return null.
  switch (operator) {
    case ">":
    case "gt":
      return compare > 0;
    case ">=":
    case "ge":
      return compare >= 0;
    case "<=":
    case "le":
      return compare <= 0;
    case "==":
    case "=":
    case "eq":
      return compare === 0;
    case "<>":
    case "!=":
    case "ne":
      return compare !== 0;
    case "":
    case "<":
    case "lt":
      return compare < 0;
    default:
      return null;
  }
};

/*********************************************************************/

/**
 * Modal
 * @type {*|Object}
 */

var Modal = Modal || {};

/**
 * Open modal
 *
 * @param e
 * @param el
 */

Modal.open = function(e, el) {
  // prevent default
  e.preventDefault();

  // prep
  var genooFrame;
  var genooTarget = el.getAttribute("data-target");
  var genooTargetInput = el.getAttribute("data-target-input");
  var genooCurrent = el.getAttribute("data-current-id");
  var genooTitle = el.getAttribute("data-title");
  var genooTitleButton = el.getAttribute("data-update-text");

  // if the frame already exists, reopen it
  if (typeof genooFrame !== "undefined") {
    genooFrame.close();
  }

  // custom uploader
  genooFrame = wp.media.frames.file_frame = wp.media({
    title: genooTitle,
    button: { text: genooTitleButton },
    multiple: false
  });

  // on select
  genooFrame.on("select", function() {
    // empty first
    document.getElementById(genooTarget).innerHTML = "";
    var attachment = genooFrame
      .state()
      .get("selection")
      .first()
      .toJSON();
    Modal.appendImage(genooTarget, attachment.url);
    document.getElementById(genooTargetInput).value = attachment.id;
    el.setAttribute("data-current-id", attachment.id);
  });

  // on open
  genooFrame.on("open", function() {
    // if there's current
    if (genooCurrent !== null) {
      var selection = genooFrame.state().get("selection");
      var attachment = wp.media.attachment(genooCurrent);
      attachment.fetch();
      selection.add(attachment);
    }
  });

  // open
  genooFrame.open();
};

/**
 * Empty image
 *
 * @param event
 * @param id
 * @param img
 * @param btn
 * @returns {boolean}
 */

Modal.emptyImage = function(event, id, img, btn) {
  event.preventDefault();
  document.getElementById(id).innerHTML = "";
  document.getElementById(img).value = "";
  document.getElementById(btn).setAttribute("data-current-id", "");
  return false;
};

/**
 * Empty Image with a placeholder
 *
 * @param event
 * @param id
 * @param img
 * @param btn
 * @returns {boolean}
 */
Modal.emptyImagePlaceholder = function(event, id, img, btn) {
  event.preventDefault();
  document.getElementById(id).innerHTML = '<div class="bContent">&nbsp;</div>';
  document.getElementById(img).value = "";
  document.getElementById(btn).setAttribute("data-current-id", "");
  return false;
};

/**
 * Append image
 *
 * @param target
 * @param src
 * @return {XML|Node}
 */

Modal.appendImage = function(target, src) {
  var elem = document.createElement("img");
  elem.setAttribute("src", src);
  return document.getElementById(target).appendChild(elem);
};

/*********************************************************************/

/**
 * Admin Helper
 *
 * @type {*|Object}
 */

var Admin = Admin || {};

/**
 * Urlencdoe
 */
Admin.urlencode = function(str) {
  str = (str + "").toString();
  return encodeURIComponent(str)
    .replace(/!/g, "%21")
    .replace(/'/g, "%27")
    .replace(/\(/g, "%28")
    .replace(/\)/g, "%29")
    .replace(/\*/g, "%2A")
    .replace(/%20/g, "+");
};

/**
 * Build Query
 *
 * @param formdata
 * @param numeric_prefix
 * @param arg_separator
 * @returns {string}
 */
Admin.buildQuery = function(formdata, numeric_prefix, arg_separator) {
  var value,
    key,
    tmp = [],
    that = this;

  var _http_build_query_helper = function(key, val, arg_separator) {
    var k,
      tmp = [];
    if (val === true) {
      val = "1";
    } else if (val === false) {
      val = "0";
    }
    if (val !== null && typeof val === "object") {
      for (k in val) {
        if (val[k] !== null) {
          tmp.push(
            _http_build_query_helper(key + "[" + k + "]", val[k], arg_separator)
          );
        }
      }
      return tmp.join(arg_separator);
    } else if (typeof val !== "function") {
      return Admin.urlencode(key) + "=" + Admin.urlencode(val);
    } else if (typeof val == "function") {
      return "";
    } else {
      throw new Error("There was an error processing for http_build_query().");
    }
  };
  if (!arg_separator) {
    arg_separator = "&";
  }
  for (key in formdata) {
    value = formdata[key];
    if (numeric_prefix && !isNaN(key)) {
      key = String(numeric_prefix) + key;
    }
    tmp.push(_http_build_query_helper(key, value, arg_separator));
  }
  return tmp.join(arg_separator);
};

/**
 * Check Disabled
 *
 * @param event
 * @param target
 * @param id
 */
Admin.checkDisabled = function(event, target, id) {
  if (event) {
    event.returnValue = null;
    if (event.preventDefault) event.preventDefault();
  }
  var element = document.getElementById(id);
  if (target.checked) {
    // Checked
    element.removeAttribute("required");
    element.value = "";
  } else {
    element.removeAttribute("required");
    element.setAttribute("required", "required");
  }
};

/**
 * WPMKTENGINE
 *
 * @version 0.4
 */

/**
 * Provide
 * @type {*|Object}
 */

var Genoo = Genoo || {};

/**
 * Theme switcher id
 * @type {String}
 */

var GenooThemeSwitcher = "genooThemeSettings-genooFormTheme";

/**
 * Theme preview id
 * @type {String}
 */

var GenooThemePreview = "genooThemeSettings-genooFormPrev";

/**
 * Impprting message
 * @type {*}
 */

var GenooImportingMessage = GenooVars.GenooMessages.importing;

/**
 * Check if element exists
 *
 * @param elem
 * @return {Boolean}
 */

Genoo.elementExists = function(elem) {
  if (elem != "undefined" && elem != null) {
    return true;
  }
  return false;
};

/**
 * Switches image
 *
 * @param to
 */

Genoo.switchImage = function(to) {
  var preUrl = window["GenooVars"] != undefined ? GenooVars : {};
  var preElem = jQuery("#" + GenooThemePreview);
  if (preUrl.GenooPluginUrl) {
    // if url is there,
    var preImage = preUrl.GenooPluginUrl + to + ".jpg";
    var preImageTag =
      '<img src="' + preImage + '?genoo=2" class="genooAdminImage" />';
    preElem.html(preImageTag);
  } else {
    Genoo.flush();
  }
};

/**
 * Flush preview image
 */

Genoo.flush = function() {
  jQuery("#" + GenooThemePreview).html("");
};

/**
 * Switch to init image
 */

Genoo.switchToInitImage = function() {
  Genoo.switchImage(
    Genoo.getCurrentValue(document.getElementById(GenooThemeSwitcher))
  );
};

/**
 * Switch to image, used with "onChange" on form
 *
 * @param elem
 */

Genoo.switchToImage = function(elem) {
  Genoo.switchImage(Genoo.getCurrentValue(elem));
};

/**
 * Get current value of a dropdown
 *
 * @param elem
 * @return {String|Number|String}
 */

Genoo.getCurrentValue = function(elem) {
  return elem.options[elem.selectedIndex].value;
};

/**
 * In array, copy of PHP in_array
 *
 * @param needle
 * @param haystack
 * @param argStrict
 * @return {Boolean}
 */

Genoo.inArray = function(needle, haystack, argStrict) {
  var key = "",
    strict = !!argStrict;
  if (strict) {
    for (key in haystack) {
      if (haystack[key] === needle) {
        return true;
      }
    }
  } else {
    for (key in haystack) {
      if (haystack[key] == needle) {
        return true;
      }
    }
  }
  return false;
};

/**
 * Is array
 *
 * @param o
 * @return {Boolean}
 */

Genoo.isArray = function(o) {
  if (o != null && typeof o == "object") {
    return typeof o.push == "undefined" ? false : true;
  } else {
    return false;
  }
};

/**
 * Start import
 *
 * @param e
 */

Genoo.startImport = function(e) {
  // prevent default click
  e.preventDefault();

  /**
   * Step 1: Start import
   */

  Genoo.startEventLog();
  Genoo.setLog();

  // call for comments info
  var data = {
    action: "genooImportStart",
    _ajax_nonce: GenooVars.AJAX_NONCE
  };

  jQuery.post(ajaxurl, data, function(response) {
    Genoo.setLog(false);

    /**
     * Step 2: If we can import, import, display next step message
     */

    Genoo.addLogMessage(response.commentsMessage, 0);

    // do we import?
    if (response.commentsStatus == true) {
      // Prep vars
      var msgs = response.commentsCount;
      var msgOffset = 0;
      var msgPer = 100;
      var msgSteps = 1;
      if (msgs > msgPer) {
        msgSteps = Math.ceil(msgs / msgPer);
      }
      var msgStep = 0;

      /**
       * Step 3: Loop through steps, catch response
       */

      Genoo.startEventLogIn();
      Genoo.addLogMessage(GenooImportingMessage);
      Genoo.setProgressBar();
      Genoo.progressBar(0);

      /**
       * Step 4: Set up interval, steps that wait for last to finish
       */

      (function importComments() {
        msgOffset = msgStep * msgPer;

        var temp = {
          action: "genooImportComments",
          _ajax_nonce: GenooVars.AJAX_NONCE,
          offset: msgOffset,
          per: msgPer
        };

        /**
         * Step 5: Add log message for each comment with success / error.
         */

        jQuery.post(ajaxurl, temp, function(importResponse) {
          if (Genoo.isArray(importResponse.messages)) {
            for (var i = 0; i < importResponse.messages.length; i++) {
              Genoo.addLogMessage(importResponse.messages[i]);
            }
          } else {
            Genoo.addLogMessage(importResponse.messages);
          }

          msgStep++;
          Genoo.progressBar(Genoo.logPercentage(msgStep, msgSteps));

          if (msgStep < msgSteps) {
            setTimeout(function() {
              importComments();
            }, 1000);
          }
        });
      })();
    }
  });
};

/*********************************************************************/

/**
 * Start subscriber import
 *
 * @param e
 */

Genoo.startSubscriberImport = function(e) {
  // prevent default click
  e.preventDefault();

  /**
   * Step 1: Start import
   */

  Genoo.startEventLog();
  Genoo.setLog();

  // call for comments info
  var data = {
    action: "genooImportSubscribersStart",
    _ajax_nonce: GenooVars.AJAX_NONCE
  };

  jQuery.post(ajaxurl, data, function(response) {
    Genoo.setLog(false);

    /**
     * Step 2: If we can import, import, display next step message
     */

    Genoo.addLogMessage(response.message, 0);

    // do we import?
    if (response.status == true) {
      // Prep vars
      var msgs = response.count;
      var msgOffset = 0;
      var msgPer = 100;
      var msgSteps = 1;
      if (msgs > msgPer) {
        msgSteps = Math.ceil(msgs / msgPer);
      }
      var leadType = Genoo.getCurrentValue(
        document.getElementById("toolsLeadTypes")
      );
      var msgStep = 0;

      /**
       * Step 3: Loop through steps, catch response
       */

      Genoo.startEventLogIn();
      Genoo.addLogMessage(GenooImportingMessage);
      Genoo.setProgressBar();
      Genoo.progressBar(0);

      /**
       * Step 4: Set up interval, steps that wait for last to finish
       */

      (function importSubscribers() {
        msgOffset = msgStep * msgPer;

        var temp = {
          action: "genooImportSubscribers",
          _ajax_nonce: GenooVars.AJAX_NONCE,
          offset: msgOffset,
          leadType: leadType,
          per: msgPer
        };

        /**
         * Step 5: Add log message for each comment with success / error.
         */

        jQuery.post(ajaxurl, temp, function(importResponse) {
          if (Genoo.isArray(importResponse.messages)) {
            for (var i = 0; i < importResponse.messages.length; i++) {
              Genoo.addLogMessage(importResponse.messages[i]);
            }
          } else {
            Genoo.addLogMessage(importResponse.messages);
          }

          msgStep++;
          Genoo.progressBar(Genoo.logPercentage(msgStep, msgSteps));

          if (msgStep < msgSteps) {
            setTimeout(function() {
              importSubscribers();
            }, 1000);
          }
        });
      })();
    }
  });
};

/**
 * Start CTA Import
 * @param event
 */
Genoo.startCTAsImport = function(event) {
  // Prevent default
  if (event.preventDefault) event.preventDefault();
  event.returnValue = null;

  /**
   * Step 1: Start import
   */

  Genoo.startEventLog();
  Genoo.setLog();

  var data = {
    action: "wpme_import_cta_count",
    _ajax_nonce: GenooVars.AJAX_NONCE
  };

  jQuery.post(ajaxurl, data, function(response) {
    /**
     * Turn of logger
     */
    Genoo.setLog(false);

    /**
     * Step 2: If we can import, import, display next step message
     */

    if (response.error) {
      // No import
      Genoo.addLogMessage(response.error, 0);
    } else {
      // Import
      Genoo.addLogMessage("Importing CTAs", 0);

      // Prep vars
      var msgs = response.found;
      var msgOffset = 0;
      var msgPer = 3;
      var msgSteps = 1;
      if (msgs > msgPer) {
        msgSteps = Math.ceil(msgs / msgPer);
      }
      var msgStep = 0;

      /**
       * Step 3: Loop through steps, catch response
       */

      Genoo.startEventLogIn();
      Genoo.addLogMessage("Started importing CTAs.");
      Genoo.setProgressBar();
      Genoo.progressBar(0);

      /**
       * Step 4: Set up interval, steps that wait for last to finish
       */

      (function importCTAS() {
        msgOffset = msgStep * msgPer;
        var temp = {
          action: "wpme_import_ctas",
          _ajax_nonce: GenooVars.AJAX_NONCE,
          offset: msgOffset,
          per: msgPer
        };

        /**
         * Step 5: Add log message for each comment with success / error.
         */

        jQuery.post(ajaxurl, temp, function(importResponse) {
          if (Genoo.isArray(importResponse.messages)) {
            for (var i = 0; i < importResponse.messages.length; i++) {
              Genoo.addLogMessage(importResponse.messages[i]);
            }
          } else {
            Genoo.addLogMessage(importResponse.messages);
          }
          msgStep++;
          Genoo.progressBar(Genoo.logPercentage(msgStep, msgSteps));
          if (msgStep < msgSteps) {
            setTimeout(function() {
              importCTAS();
            }, 1000);
          }
        });
      })();
    }
  });
};

/**
 * Log percentage calc
 *
 * @param step
 * @param steps
 * @return {Number}
 */

Genoo.logPercentage = function(step, steps) {
  return (step / steps) * 100;
};

/**
 * Start event log
 */

Genoo.startEventLog = function() {
  jQuery("#genooLog").remove();
  jQuery(".metabox-holder").prepend(
    '<div id="genooLog" class="strong update-nag">' +
      '<div id="genooHeader"></div>' +
      "</div>"
  );
};

/**
 * Event log in
 */

Genoo.startEventLogIn = function() {
  return jQuery("#genooLog").append('<div id="genooLogIn"></div>');
};

/**
 * Start progress bar
 *
 * @param yes
 * @return {*}
 */

Genoo.setProgressBar = function(yes) {
  if (yes == false) {
    return jQuery("#genooProgressBar").remove();
  }
  return jQuery("#genooLog").append(
    '<div id="genooProgressBar"><span id="genooProgressBarBG" class="button button-primary"></span><span id="genooProgressBarText"></span></div>'
  );
};

/**
 * Progress bar
 * @param perc
 */

Genoo.progressBar = function(perc) {
  var cailed = Math.ceil(perc);
  document.getElementById("genooProgressBarText").innerHTML = cailed + "%";
  document.getElementById("genooProgressBarBG").style.width = cailed + "%";
};

/**
 * Add a log message
 *
 * @param message
 * @param type
 */

Genoo.addLogMessage = function(message, type) {
  if (type == 0) {
    return jQuery("#genooHeader").append(
      "<h3>" + message + '</h3><div class="clear"></div>'
    );
  }
  return jQuery("#genooLogIn").append(
    "<small>" + message + '</small><div class="clear"></div>'
  );
};

/**
 * Set WPMKTENGINE log
 *
 * @param log
 * @return {*}
 */

Genoo.setLog = function(log) {
  if (log == false) {
    return jQuery("#genooLoading").remove();
  }
  return jQuery("#genooLog").append(
    '<div id="genooLoading" class="genooLoading"></div>'
  );
};

/**
 * WPMKTENGINE init
 */

Genoo.init = function() {
  if (Genoo.elementExists(document.getElementById(GenooThemeSwitcher))) {
    Genoo.switchToImage(document.getElementById(GenooThemeSwitcher));
  }
};

/**
 * Jquery document ready (init)
 */

jQuery(document).ready(function() {
  Genoo.init();
});

/**
 * Slides
 * @type {{}}
 */
var WPMESlide = WPMESlide || {};

/**
 * Go To
 *
 * @param element
 * @param event
 * @param target
 * @param dontblock
 */
WPMESlide.goTo = function(element, event, target, dontblock) {
  // Prevent
  if (!dontblock) {
    event.returnValue = null;
    if (event.preventDefault) event.preventDefault();
  }
  // Direction
  var direction = Tool.hasClass(element, "prev") ? "prev" : "next";
  // Go to
  var intTarget = target;
  var intCurrent = element.getAttribute("data-current");
  // Here we go
  Tool.addClass(document.getElementById("wpme_row_id_" + intCurrent), "hidden");
  Tool.removeClass(
    document.getElementById("wpme_row_id_" + intTarget),
    "hidden"
  );
  // Check radio!
  document.getElementById("_wpme_modal_theme_" + intTarget).checked = true;
};

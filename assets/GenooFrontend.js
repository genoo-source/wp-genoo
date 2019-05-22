
/**
 * Genoo Frontend
 *
 * @version 1.0
 * @author Genoo LLC
 */


/*********************************************************************/

/**
 * Fade effects
 */

(function(){
    var FX = {
        easing: {
            linear: function(progress) {
                return progress;
            },
            quadratic: function(progress) {
                return Math.pow(progress, 2);
            },
            swing: function(progress){
                return 0.5 - Math.cos(progress * Math.PI) / 2;
            },
            circ: function(progress){
                return 1 - Math.sin(Math.acos(progress));
            },
            back: function(progress, x){
                return Math.pow(progress, 2) * ((x + 1) * progress - x);
            },
            bounce: function(progress){
                for (var a = 0, b = 1, result; 1; a += b, b /= 2) {
                    if (progress >= (7 - 4 * a) / 11) {
                        return -Math.pow((11 - 6 * a - 11 * progress) / 4, 2) + Math.pow(b, 2);
                    }
                }
            },
            elastic: function(progress, x){
                return Math.pow(2, 10 * (progress - 1)) * Math.cos(20 * Math.PI * x / 3 * progress);
            }
        },
        animate: function(options){
            var start = new Date;
            var id = setInterval(function() {
                var timePassed = new Date - start;
                var progress = timePassed / options.duration;
                if (progress > 1) {
                    progress = 1;
                }
                options.progress = progress;
                var delta = options.delta(progress);
                options.step(delta);
                if (progress == 1) {
                    clearInterval(id);
                    options.complete();
                }
            }, options.delay || 10);
        },
        fadeOut: function(element, options){
            var to = 1;
            this.animate({
                duration: options.duration,
                delta: function(progress) {
                    progress = this.progress;
                    return FX.easing.swing(progress);
                },
                complete: options.complete,
                step: function(delta) {
                    element.style.opacity = to - delta;
                    element.style.filter = 'alpha(opacity=' + (100 * (to - delta))|0 + ')'
                }
            });
        },
        fadeIn: function(element, options){
            var to = 0;
            element.style.display = 'block'
            this.animate({
                duration: options.duration,
                delta: function(progress) {
                    progress = this.progress;
                    return FX.easing.swing(progress);
                },
                complete: options.complete,
                step: function(delta) {
                    element.style.opacity = to + delta;
                    element.style.filter = 'alpha(opacity=' + (100 * (to + delta))|0 + ')'
                }
            });
        }
    };
    window.FX = FX;
})();


/*********************************************************************/

/**
 * Tools
 * @type {*|Object}
 */

var Tool = Tool || {};


/**
 * Check if element exists
 *
 * @param elem
 * @return {Boolean}
 */

Tool.elementExists = function(elem){ if(elem.length > 0){ return true; } else { return false; } };


/**
 * Remove active class
 *
 * @param from
 * @param className
 */

Tool.removeAllClassOf = function(from, className) {
    var list_items = document.getElementById(from).childNodes;
    for (var i=0, j=list_items.length; i<j; i++){
        var elm = list_items[i];
        if(Tool.hasClass(elm, className)){
            Tool.removeClass(elm, className);
        }
    }
};


/**
 * Switch display state
 *
 * @param element
 */

Tool.switchDisplay = function(element)
{
    if(element.style.display == 'none'){
        element.style.display = '';
    } else {
        element.style.display = 'none';
    }
};


/**
 * Has class
 *
 * @param el
 * @param className
 * @return {Boolean}
 */

Tool.hasClass = function(el, className)
{
    if (el.classList)
        return el.classList.contains(className);
    else
        return new RegExp('(^| )' + className + '( |$)', 'gi').test(el.className);
};


/**
 * Add class
 *
 * @param el
 * @param className
 */

Tool.addClass = function(el, className)
{
    if (el.classList)
        el.classList.add(className);
    else
        el.className += ' ' + className;
};


/**
 * Remove class
 *
 * @param el
 * @param className
 */

Tool.removeClass = function(el, className)
{
    if (el.classList)
        el.classList.remove(className);
    else
        el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
};

/**
 * Switch class
 *
 * @param element
 * @param className
 */

Tool.switchClass = function(element, className)
{
    if(Tool.hasClass(element, className)){
        Tool.removeClass(element, className);
    } else {
        Tool.addClass(element, className);
    }
};


/**
 * Get widnow size
 *
 * @returns {{x: (Number|number), y: (Number|number)}}
 */

Tool.windowSize = function()
{
    var w = window,
        d = document,
        e = d.documentElement,
        g = d.getElementsByTagName('body')[0],
        x = w.innerWidth || e.clientWidth || g.clientWidth,
        y = w.innerHeight|| e.clientHeight|| g.clientHeight;
    return { x: x, y: y };
};


/*********************************************************************/

/**
 * Tools
 * @type {*|Object}
 */

var Element = Element || {};


/**
 * Element position
 *
 * @param element
 * @return {Object}
 */

Element.position = function(element){
    var xPosition = 0;
    var yPosition = 0;
    while(element) {
        xPosition += (element.offsetLeft - element.scrollLeft + element.clientLeft);
        yPosition += (element.offsetTop - element.scrollTop + element.clientTop);
        element = element.offsetParent;
    }
    return { x: xPosition, y: yPosition };
};


/**
 * Get element heig
 *
 * @param element
 * @returns {number}
 */

Element.height = function(element)
{
    return element.offsetHeight;
};


/*********************************************************************/

/**
 * Modal windows
 * @type {*|Object}
 */

var Modal = Modal || {};


/**
 * Center modal
 *
 * @param modal
 */

Modal.center = function(modal)
{
    var modalHeight = modal.offsetHeight;
    var windowHeight = Tool.windowSize().y;
    var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
    if(modalHeight > windowHeight){
        // we're going overflow
        Tool.addClass(modal, 'genooOverflow');
        // get guts
        var modalInsides = document.querySelectorAll('#' + modal.id + ' .genooGuts');
        var modalInsidesOne = modalInsides[0];
        // set all needed
        if(w == 625 || w < 625){
            window.scrollTo(0,0);
        } else {
            modalInsidesOne.style.height = (windowHeight - 150) + 'px';
        }
        modal.style.marginTop= '-' + (modal.offsetHeight / 2) + 'px';
    } else {
        // just cebter if no problem with height
        modal.style.marginTop= '-' + (modalHeight / 2) + 'px';
    }
};



/**
 * Display
 *
 * @param e
 * @param modalId
 */

Modal.display = function(e, modalId)
{
    if(e){
        if(e.preventDefault) e.preventDefault();
        if(e.returnValue) e.returnValue = null;
    }
    var doc = document.documentElement, body = document.body;
    var top = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
    var modal = document.getElementById(modalId);
    var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
    if(modalId !== null){
        FX.fadeIn(document.getElementById('genooOverlay'),{
            duration: 400,
            complete: function(){
                Tool.switchClass(document.getElementById('genooOverlay'), 'visible');
                Tool.addClass(modal, 'visible');
                Modal.center(modal);
                Tool.addClass(document.body, 'genooModalOpen');
                if(w == 625 || w < 625){
                    window.scrollTo(0,0);
                }
            }
        });
    }
};


/**
 * Close
 *
 * @param e
 * @param modalId
 */

Modal.close = function(e, modalId)
{
    e.preventDefault();
    var modalOverlay = document.getElementById('genooOverlay');
    FX.fadeOut(modalOverlay, {
        duration: 400,
        complete: function(){
            Tool.switchClass(document.getElementById('genooOverlay'), 'visible');
            modalOverlay.style.display = 'none';
            Tool.removeAllClassOf('genooOverlay', 'visible');
            Tool.removeClass(document.body, 'genooModalOpen');
            // After all hidden, reset the forms
            Modal.resetForms();
        }
    });
};

/**
 * Reset forms
 */
Modal.resetForms = function(){
    // get forms
    var forms = document.getElementById("genooOverlay").getElementsByTagName('form');
    if(forms.length > 0){
        for(i = 0;i < forms.length; i++) {
            // Reset
            forms[i].reset();
        }
    }
};

/**
 * Document
 * @type {*|Object}
 */

var Document = Document || {};

/**
 * Document ready function
 *
 * @author Diego Perini (diego.perini at gmail.com)
 *
 * @param win
 * @param fn
 */

Document.ready = function(win, fn)
{
    var done = false, top = true,
        doc = win.document, root = doc.documentElement,
        add = doc.addEventListener ? 'addEventListener' : 'attachEvent',
        rem = doc.addEventListener ? 'removeEventListener' : 'detachEvent',
        pre = doc.addEventListener ? '' : 'on',
        init = function(e) {
            if (e.type == 'readystatechange' && doc.readyState != 'complete') return;
            (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
            if (!done && (done = true)) fn.call(win, e.type || e);
        },
        poll = function() {
            try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
            init('poll');
        };
    if (doc.readyState == 'complete') fn.call(win, 'lazy');
    else {
        if (doc.createEventObject && root.doScroll) {
            try { top = !win.frameElement; } catch(e) { }
            if (top) poll();
        }
        doc[add](pre + 'DOMContentLoaded', init, false);
        doc[add](pre + 'readystatechange', init, false);
        win[add](pre + 'load', init, false);
    }
};


/**
 * Counter
 * @type {*|Object}
 */

var Counter = Counter || {};

/**
 * Attach timer
 *
 * @param date
 * @param element
 */
Counter.attach = function(date, element)
{
    var e = document.getElementById(element);
    if (typeof(e) != 'undefined' && e != null){
        var d,h,m,s;
        interval = window.setInterval(function(){
            var now = new Date();
            start_date = now.getTime();
            //end_date = new Date(2015, 9-1, 29, 12); //Need to make this the correct date
            end_date = new Date(date); //Need to make this the correct date
            // year, month, day, hours, minutes, seconds
            d=0;
            h=0;
            m=0;
            s=0;
            time_left=end_date-start_date;
            while(time_left > 1000*60*60*24){
                d++;
                time_left-=1000*60*60*24;
            }
            while(time_left > 1000*60*60){
                h++;
                time_left-=1000*60*60;
            }
            while(time_left > 1000*60){
                m++;
                time_left-=1000*60;
            }
            while(time_left > 1000){
                s++;
                time_left-=1000;
            }
            if(start_date < end_date){
                Counter.update([d,h,m,s], e);
            } else {
                Counter.update([d,h,m,s], e);
                clearInterval(interval);
            }
        }, 1000);
    }
};

/**
 * Update elements
 * @param date
 * @param element
 */
Counter.update = function(date, element)
{
    // Get elements
    var days = element.getElementsByClassName('days')[0];
    var hours = element.getElementsByClassName('hours')[0];
    var minutes = element.getElementsByClassName('minutes')[0];
    var seconds = element.getElementsByClassName('seconds')[0];
    // Set values
    days.innerHTML="<span>"+date[0]+"</span> Days";
    hours.innerHTML="<span>"+date[1]+"</span> Hours";
    minutes.innerHTML="<span>"+date[2]+"</span> Min";
    seconds.innerHTML="<span>"+date[3]+"</span> Sec";
};

/**
 * CounterBuilder
 * @type {*|Object}
 */

var CounterBuilder = CounterBuilder || {};

/**
 * Attach timer
 *
 * @param date
 * @param element
 */
CounterBuilder.attach = function(date, element)
{
    var e = document.getElementById(element);
    if (typeof(e) != 'undefined' && e != null){
        var d,h,m,s;
        interval = window.setInterval(function(){
            var now = new Date();
            start_date = now.getTime();
            end_date = new Date(date);
            d=0;
            h=0;
            m=0;
            s=0;
            time_left=end_date-start_date;
            // Days
            while(time_left > 1000*60*60*24){
                d++;
                time_left-=1000*60*60*24;
            }
            // Hours
            while(time_left > 1000*60*60){
                h++;
                time_left-=1000*60*60;
            }
            // Minutes
            while(time_left > 1000*60){
                m++;
                time_left-=1000*60;
            }
            // Seconds
            while(time_left > 1000){
                s++;
                time_left-=1000;
            }
            if(start_date < end_date){
                CounterBuilder.update([d,h,m,s], e);
            } else {
                CounterBuilder.update([d,h,m,s], e);
                clearInterval(interval);
            }
        }, 1000);
    }
};

/**
 * Update elements
 * @param date
 * @param element
 */
CounterBuilder.update = function(date, element)
{
    // Get elements
    var days = element.getElementsByClassName('days')[0];
    var hours = element.getElementsByClassName('hours')[0];
    var minutes = element.getElementsByClassName('minutes')[0];
    var seconds = element.getElementsByClassName('seconds')[0];
    // Set values
    days.childNodes[0].innerHTML=date[0];
    hours.childNodes[0].innerHTML=date[1];
    minutes.childNodes[0].innerHTML=date[2];
    seconds.childNodes[0].innerHTML=date[3];
};

// Move out of modal wrapper
// Mover after body
// Add class afterbody

/**
 * Genoo CSS
 * @type {GenooCSS|*|{}}
 */
var GenooCSS = GenooCSS || {};

/**
 * Add CSS
 * @param css
 */
GenooCSS.add = function(css)
{
    var styleElement = document.createElement("style");
    styleElement.type = "text/css";
    if (styleElement.styleSheet) {
        styleElement.styleSheet.cssText = css;
    } else {
        styleElement.appendChild(document.createTextNode(css));
    }
    document.getElementsByTagName("head")[0].appendChild(styleElement);
};
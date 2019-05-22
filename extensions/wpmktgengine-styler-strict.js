"use strict";

/**
 * Modded attributeForm
 * @constructor attributeForm
 * @param paramaters {Object}
 */

var attributeForm = function (paramaters) {

    var heading = paramaters.heading;
    var attributes = paramaters.attributes;
    var colorPicker = paramaters.colorPicker;
    var attachedObject;
    this.targetElements = {};
    this.parentElement = null;

    // /**
    //  * Placeholder in case someone wants top preform any actions before attachting
    //  * to a node (ex. removing styles before harvesting their values)
    //  * @param Node
    //  */
    // this.doBeforeAttach = function( node ){}

    /**
     * Updates any attributes the form edits to be set to the value on the node.
     * sets this.attachedObject
     * @param node
     */
    this.attach = function (node) {

        // Remove hover styles
        // hoverStyleNodes = document.querySelectorAll('[data-hover-style]');
        // for ( var i=0; i < hoverStyleNodes.length; i++ ) {
        // 	newStyle = hoverStyleNodes[i].getAttribute('style')
        // 						 	 .replace(/\/\*HOVER\*\/.*/gi, '');
        // 	hoverStyleNodes[i].setAttribute( 'style', newStyle );
        // }


        this.attachedObject = null;

        var exampleNode;
        // Check if a single node was passed
        if (node.tagName) {
            exampleNode = node;
        } else {
            // A list of nodes were attached, use the first one as an example.
            exampleNode = ( !node[0].checked || !node[1] ) ? node[0] : node[1];
        }

        if (exampleNode.getAttribute('style')) {
            exampleNode.setAttribute(
                'style',
                exampleNode.getAttribute('style').replace(/\/\*HOVER\*\/.*/gi, '')
            );
        }

        // Make each item listen for an update value call
        // TODO: Support for array targets
        var parsedTargetValue;
        var target;
        for (target in this.targetElements) {
            if (typeof target == 'string' && target.indexOf('{Array}') == -1) {
                // Send an event to every form item to update it's value
                parsedTargetValue = window.getComputedStyle(exampleNode).getPropertyValue(target);
                if (typeof parsedTargetValue != 'string' || parsedTargetValue == '') {
                    parsedTargetValue = exampleNode.style[target];
                }

            } else {
                var targetOnion = JSON.parse(target.replace('{Array}', ''));
                var parsedTarget = exampleNode;
                for (var step = 0; step < targetOnion.length - 1; step++) {
                    if (targetOnion[step] == 'style') {
                        // Here, we are reading the styles, so in the same way that we read
                        // "top-level" target styles with getComputedStyle, we do this with
                        // "lower-level" targets as well

                        // Our custom hover-XYZ attributes arent caught with
                        // getComputedStyle
                        if (window.getComputedStyle(parsedTarget)) {
                            parsedTarget = window.getComputedStyle(parsedTarget);
                        } else {
                            parsedTarget = parsedTarget.style;
                        }

                    } else {
                        parsedTarget = parsedTarget[targetOnion[step]];
                    }
                }
                parsedTargetValue = parsedTarget[targetOnion[step]];

            }

            // Make IE happy
            (function () {
                function CustomEvent(event, params) {
                    params = params || {bubbles: false, cancelable: false, detail: undefined};
                    var evt = document.createEvent('CustomEvent');
                    evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
                    return evt;
                }
                CustomEvent.prototype = window.Event.prototype;
                window.CustomEvent = CustomEvent;
            })();
            // Re write the values
            this.targetElements[target].dispatchEvent(
                new CustomEvent(
                    "updateValue",
                    {
                        detail: {
                            value: parsedTargetValue
                        },
                        bubbles: true,
                        cancelable: true
                    }
                )
            );
        }

        // Change this last, so that if this.update is called, none of the previous
        // element's styles leak over
        this.attachedObject = node;
    };


    this.getAttachedObject = function () {
        return this.attachedObject;
    };

    /**
     * Listens for update event and then updates the first childNode's value. Applying keyup, mousup and change
     */
    this.listenForUpdate = function (formElement, returns) {
        formElement.addEventListener(
            'updateValue',
            function (e) {

                var result = e.detail.value;
                if (!result) {
                    console.warn('BAD VALUE: ', formElement);
                }
                formElement.setAttribute('data-value', result);

                // Most returns are strings, specifying a unit
                if (typeof returns == 'string') {
                    var returnValue = returns.split('%val%');
                    var getValue = new RegExp(
                        attributeForm.tools.escapeRegex(returnValue[0]) +
                        '(.*)' +
                        attributeForm.tools.escapeRegex(returnValue[1]));
                    if (result && !result.match(getValue)) {
                        console.warn('Incorrect value for returns on object, converting ' +
                            result + ' to a number');
                        getValue = parseInt(result);
                    }
                    var updateValue = result.match(getValue)[1];

                    // Some browsers are *really* picky on what you give their number
                    // inputs (looking at you chrome). To make them happy, we convert our
                    // numbers to... numbers. I guesss thats not asking too much.
                    if (this.childNodes[0].getAttribute('type') == 'number' &&
                        typeof updateValue == 'string') {
                        if (updateValue.indexOf('.') > -1) {
                            updateValue =
                                Math.floor(parseFloat(updateValue) * 100) / 100;
                        } else {
                            updateValue = parseInt(updateValue);
                        }
                    }

                    this.childNodes[0].value = updateValue;
                }

                // If it isnt a string then this is a checkbox
                if (typeof returns == 'object') {
                    this.childNodes[0].checked = returns[true] == result;
                }

                // IDEA: Could returns be a function?
                if (typeof returns == 'function') {
                    console.warn('returns attribute as function is not *yet* supported');
                }

            }
        );
    };

    /**
     * The function that creates each form type.
     */
    this.createForm = function (paramaters) {
        this.formElementDefintions = {

            text: function (paramaters) {
                var returnValue;
                var inputElement = document.createElement('div');
                inputElement.className = 'gn-input';

                var inputFeildElement = document.createElement('input');
                inputFeildElement.className = 'gn-input__feild';
                inputFeildElement.setAttribute('type', 'text');
                if (paramaters.placeholder) {
                    inputFeildElement.setAttribute('placeholder', paramaters.placeholder);
                }
                inputElement.appendChild(inputFeildElement);

                // Always update the value of the main element when value changes
                inputFeildElement.addEventListener(
                    'keyup',
                    function () {
                        if (paramaters.returns) {
                            returnValue = paramaters.returns.replace(/%val%/gi, this.value);
                        } else {
                            returnValue = this.value
                        }
                        this.parentElement.setAttribute('data-value', returnValue);
                    }
                );

                return inputElement;
            },

            number: function (paramaters) {

                var inputElement = document.createElement('div');
                inputElement.className = 'gn-input';

                var inputFeildElement = document.createElement('input');
                inputFeildElement.className = 'gn-input__feild';
                inputFeildElement.setAttribute('type', 'number');
                if (paramaters.placeholder) {
                    inputFeildElement.setAttribute('placeholder', paramaters.placeholder);
                }
                if (paramaters.min) {
                    inputFeildElement.setAttribute('min', paramaters.min);
                }
                if (paramaters.max) {
                    inputFeildElement.setAttribute('max', paramaters.max);
                }
                if (paramaters.step) {
                    inputFeildElement.setAttribute('step', paramaters.step);
                }

                inputElement.appendChild(inputFeildElement);
                inputFeildElement.addEventListener(
                    'change',
                    function () {
                        var returnValue;
                        if (paramaters.returns) {
                            returnValue = paramaters.returns.replace(/%val%/gi, this.value);
                        } else {
                            returnValue = this.value
                        }
                        this.parentElement.setAttribute('data-value', returnValue);
                    }
                );

                return inputElement;
            },

            dropdown: function (paramaters) {
                var dropdownElement = document.createElement('div');
                dropdownElement.className = 'gn-dropdown';

                var selectElement = document.createElement('select');
                selectElement.className = 'gn-dropdown__select';
                if (paramaters.placeholder) {
                    selectElement.setAttribute('placeholder', paramaters.placeholder);
                }
                dropdownElement.appendChild(selectElement);

                if (paramaters.emptyState) {
                    var emptyState = document.createElement('option');
                    emptyState.setAttribute('disabled', true);
                    emptyState.setAttribute('selected', true);
                    emptyState.appendChild(
                        document.createTextNode(paramaters.emptyState)
                    );
                    selectElement.appendChild(emptyState);
                }

                if (paramaters.options) {
                    var optionElement, option, option_i;
                    for (option_i = 0; option_i < paramaters.options.length; option_i++) {
                        option = paramaters.options[option_i];
                        optionElement = document.createElement('option');
                        optionElement.setAttribute('value', option.value);
                        optionElement.appendChild(document.createTextNode(option.name));
                        selectElement.appendChild(optionElement);
                    }
                } else {
                    console.warn('WARNING: dropdownElement created with no options');
                }

                selectElement.addEventListener(
                    'change',
                    function () {
                        var returnValue;
                        if (paramaters.returns) {
                            returnValue = paramaters.returns.replace(/%val%/gi, this.value);
                        } else {
                            returnValue = this.value
                        }
                        this.parentElement.setAttribute('data-value', returnValue);
                    }
                );
                return dropdownElement;
            },

            toggle: function (paramaters) {
                var toggleElement = document.createElement('div');
                toggleElement.className = 'gn-toggle';

                var checkboxElement = document.createElement('input');
                checkboxElement.className = 'gn-toggle__checkbox';
                checkboxElement.setAttribute('type', 'checkbox');

                var handleBarElem = document.createElement('div');
                handleBarElem.className = 'gn-toggle__handle-bar';
                var handleElem = document.createElement('div');
                handleElem.className = 'gn-toggle__handle';
                var stateElem = document.createElement('div');
                stateElem.className = 'gn-toggle__state';

                toggleElement.appendChild(checkboxElement);
                toggleElement.appendChild(handleBarElem);
                toggleElement.appendChild(handleElem);
                toggleElement.appendChild(stateElem);

                // Always update the value of the main element when value changes
                checkboxElement.addEventListener(
                    'change',
                    function () {
                        //this.value;
                        var returnValue = paramaters.returns[this.checked];
                        this.parentElement.setAttribute('data-value', returnValue);
                    }
                );

                return toggleElement;
            },

            // <div class="gn-color">
            //	 <input type="text" class="gn-color__input" value="#016699"/>
            //	 <div class="gn-color__swatch" style="background-color: #016699"></div>
            // </div>

            color: function (paramaters) {
                var defaultColor = '#ffffff';

                var colorElement = document.createElement('div');
                colorElement.className = 'gn-color';

                var colorSwatch = document.createElement('div');
                colorSwatch.style.backgroundColor = defaultColor;
                colorSwatch.className = 'gn-color__swatch';


                var colorInput = document.createElement('input');
                colorInput.setAttribute('type', 'text');
                colorInput.value = defaultColor;
                colorInput.className = 'gn-color__input';

                var updateColor = function (color) {
                    var newColor = attributeForm.tools.toHex(color);
                    colorSwatch.style.backgroundColor = newColor;
                    // Change the preview color
                    colorElement.querySelectorAll('.wp-color-result')[0]
                        .style.backgroundColor = color;
                    colorInput.value = newColor;
                    jQuery(colorInput).trigger('keyup');
                    colorElement.setAttribute('data-value', newColor);
                };

                colorSwatch.addEventListener(
                    'click',
                    function () {
                        colorPicker({
                            color: colorInput.value,
                            colorElem: colorElement,
                            callBack: function (newColor) {
                                updateColor(newColor);
                            }
                        })
                    }
                );

                colorElement.addEventListener(
                    'updateValue',
                    function (e) {
                        updateColor(e.detail.value);
                    }
                );

                colorElement.appendChild(colorSwatch);
                colorElement.appendChild(colorInput);

                jQuery(colorInput).wpColorPicker({
                    mode: 'hex',
                    change: function (event, ui) {
                        updateColor(event.target.value);
                    }
                });

                return colorElement;

            }

        };
        return this.formElementDefintions[paramaters.type](paramaters);
    };

    /**
     * @param styleNode node who's styles are being changed
     */
    this.applyStyles = function (styleNode) {
        if (!styleNode) return;

        var isNodeList = (styleNode.tagName) ? false : true;
        var target;
        for (target in this.targetElements) {

            if (isNodeList) {

                var n_i;

                // If it is a string, assume a style value
                if (target.indexOf('{Array}') == -1) {
                    for (n_i = 0; n_i < styleNode.length; n_i++) {
                        styleNode[n_i].style[target] =
                            this.targetElements[target].getAttribute('data-value');
                    }
                }

                // target = [ 'style', 'border-radius' ]
                // If not, loop through the list to
                if (target.indexOf('{Array}') > -1) {
                    var targetOnion = JSON.parse(target.replace('{Array}', ''));

                    for (n_i = 0; n_i < styleNode.length; n_i++) {
                        var parsedTarget = styleNode[n_i];
                        for (var step = 0; step < targetOnion.length - 1; step++) {
                            parsedTarget = parsedTarget[targetOnion[step]];
                        }
                        parsedTarget[targetOnion[step]] =
                            this.targetElements[target].getAttribute('data-value');
                    }
                }

            } else {

                // If it is a string, assume a style value
                if (target.indexOf('{Array}') == -1) {
                    styleNode.style[target] =
                        this.targetElements[target].getAttribute('data-value');
                }

                // target = [ 'style', 'border-radius' ]
                // If not, loop through the list to
                if (target.indexOf('{Array}') > -1) {
                    var targetOnion = JSON.parse(target.replace('{Array}', ''));
                    var parsedTarget = styleNode;
                    for (var step = 0; step < targetOnion.length - 1; step++) {
                        parsedTarget = parsedTarget[targetOnion[step]];
                    }
                    parsedTarget[targetOnion[step]] =
                        this.targetElements[target].getAttribute('data-value');
                }

            }
        }
    };

    /**
     * Apply the styles from the attribute form to the attached object(s)
     */
    this.update = function () {
        this.applyStyles(this.attachedObject);
    };

    /**
     * Remove any current instances of the form and place it on the node passed
     * as a paramater
     * @param parentElement
     */
    this.appendTo = function (parentElement) {
        if (!parentElement || !parentElement.tagName) {
            console.warn(parentElement);
            throw 'incorrect value given for parentElement';
        }

        this.parentElement = parentElement;

        if (heading || attributes.heading) {
            var headingElem;
            headingElem = document.createElement('h3');
            headingElem.appendChild(
                document.createTextNode(heading)
            );
            parentElement.appendChild(headingElem);
        } else {

        }

        var currentAttribute;
        var formElement;
        var newLabel;
        var attribute_i;
        var formType;
        for (attribute_i = 0; attribute_i < attributes.length; attribute_i++) {
            currentAttribute = attributes[attribute_i];

            // Add the label if defined by the attribute
            if (currentAttribute.label) {
                newLabel = document.createElement('label');
                newLabel.className = 'gn-label';
                newLabel.appendChild(
                    document.createTextNode(currentAttribute.label)
                );
                // Add a helper if defined by the attribute
                if (currentAttribute.helper) {
                    var newLabelHelper;
                    var newLabelTooltip;
                    newLabelHelper = document.createElement('span');
                    newLabelHelper.className = 'gn-label__helper';
                    newLabelTooltip = document.createElement('span');
                    newLabelTooltip.className = 'gn-label__tooltip';
                    newLabelTooltip.appendChild(
                        document.createTextNode(currentAttribute.helper)
                    );
                    newLabelHelper.appendChild(newLabelTooltip);
                    newLabel.appendChild(newLabelHelper)
                }
                // Add Label to the parentElement
                parentElement.appendChild(newLabel);
            }

            formType = currentAttribute.type;
            formElement = this.createForm(currentAttribute);

            // Associate the newly created element with its target
            var targetName = currentAttribute.target;
            if (typeof targetName != 'string') {
                targetName = '{Array}' + JSON.stringify(targetName);
            }
            this.targetElements[targetName] = formElement;

            // Listen if any changes need to happen to the object
            if (!currentAttribute.returns) {
                currentAttribute.returns = '%val%'
            }

            this.listenForUpdate(formElement, currentAttribute.returns);

            // Add form to the parentElement
            parentElement.appendChild(formElement);
        }
    }
};

attributeForm.tools = {
    toHex: function (rgb) {
        if (!rgb) {
            console.warn('attributeForm.tools.toHex given bad value');
            return '#ffffff';
        }
        var hexMatch = rgb.match(/#?([a-fA-F0-9]{6})|([a-fA-F0-9]{3})/);
        if (hexMatch && rgb.indexOf('rgb') == -1) {
            var newHex = hexMatch[0].replace(/#/gi, '');
            if (newHex.length == 3) {
                return '#' + newHex.replace(/(.)/gi, '$1$1');
            }
            return '#' + newHex;
        }

        rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
        return (rgb && rgb.length === 4) ? "#" +
        ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
        ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
        ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2) : '';
    },
    escapeRegex: function (str) {
        return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
    }
};


var Customizer = Customizer || {};

/**
 * Range input slider
 *
 * @param that
 */
Customizer.range = function (that) {
    // Display range change
    var target = document.getElementById(that.getAttribute('data-target'));
    var value = that.value;
    target.innerHTML = '(' + value + ')';
    // Append to target elemetn
    var targetElement = (that.getAttribute('data-target-element'));
    var targetProperty = (that.getAttribute('data-target-property'));
    var targetSuffix = (that.getAttribute('data-target-suffix'));
    // If all this is, change, bam
    if (targetElement && targetProperty) {
        document.getElementById(targetElement).style[targetProperty] = value + targetSuffix;
    }
};

/**
 * Color Picker
 *
 * @param event
 * @param ui
 */
Customizer.colorPicker = function (event, ui) {
    // Value
    var value = ui.color.toString();
    // Target
    var targetElement = (event.target.getAttribute('data-target-element'));
    var targetProperty = (event.target.getAttribute('data-target-property'));
    var targetSuffix = (event.target.getAttribute('data-target-suffix'));
    // If all this is, change, bam
    if (targetElement && targetProperty) {
        document.getElementById(targetElement).style[targetProperty] = value + targetSuffix;
    }
};

/**
 * Inline Style to Object
 *
 * @param node
 * @returns {{}}
 */
Customizer.inlineStyleToObject = function (node) {
    if (!node || !node.getAttribute('style')) {
        console.warn('No styles found', arguments);
        return {};
    }

    // Get all of the inline styles of the node
    var inlineStyle = node.getAttribute('style');
    var rules = inlineStyle.split(/; ?/gi),
        object = {},
        rule;

    for (var i = 0; i < rules.length; i++) {
        rule = rules[i].split(/: ?/gi);
        if (rule[0].length > 0) {
            object[rule[0]] = rule[1];
        }
    }
    return object;
};

/**
 * On load
 */
Customizer.onLoad = function () {
    // Get Data
    var data = document.getElementById('content-textarea').innerHTML;
    if (data) {
        // JSON?
        var dataValid = Customizer.isValidJson(data);
        if (dataValid !== false) {
            // We have a valid JSON
            // Go through selectors
            try {
                for (var prop in dataValid){
                    if (dataValid.hasOwnProperty(prop)){
                        // Now we have a selector
                        var dataSelector = prop.replace(/&gt;/gi, '>');
                        var dataStyles = dataValid[prop];
                        // Lets go through each style
                        for (var style in dataStyles) {
                            if (dataStyles.hasOwnProperty(style)) {
                                // With style and selector we can apply the styles
                                // Try to apply CSS
                                jQuery(dataSelector).css(style, dataStyles[style]);
                            }
                        }
                    }
                }
            } catch (Error){
                // Doesn't seem to work for this borwser, do not care
            }
        }
    }
};

/**
 * Valid JSON?
 *
 * @param str
 * @returns {boolean}
 */
Customizer.isValidJson = function (str) {
    try {
        return JSON.parse(str);
    } catch (e) {
        return false;
    }
};

/**
 * Update Labels
 * @param event
 * @param that
 */
Customizer.updateLabels = function (event, that) {
    // Continue
    var $labelsConnectedToAnInput;
    if (event.target.checked) {

        // Find all of the labels connected to an input
        $labelsConnectedToAnInput = jQuery('label + input[type="text"], label + textarea').prev();
        $labelsConnectedToAnInput.each(function () {
            this.nextElementSibling.setAttribute(
                'placeholder',
                this.innerText
            );
        }).css('display', 'none');

    } else {
        // Find all of the labels connected to an input
        $labelsConnectedToAnInput = jQuery('label + input[type="text"], label + textarea').prev();
        $labelsConnectedToAnInput.each(function () {
            this.nextElementSibling.setAttribute(
                'placeholder',
                ''
            );
        }).css('display', 'initial');
    }
};

// Cause the above event right on page load;
window.onload = function () {
    // TODO: Good lord this is dreadful...
    jQuery('#wpmktengine_style_make_placeholders').click().click();
};

/**
 * Customizer update
 *
 * @returns object
 */
Customizer.update = function () {
    // Give me styles like this in this function, and I will handle the rest buddy
    if (jQuery(this).is('.progress')) {
        var type = 'Progress Bar';
        jQuery('#editor-wrapper *[id$="editor"]').hide();
        jQuery("#text-editor").show();
        jQuery("#progress-bar-editor").show();
        textSettings.attach(jQuery(this).find('.progress-per')[0]);
        progressBarSettings.attach(this);
    }

    var styles = {
        '#genooOverlay': Customizer.inlineStyleToObject(
            jQuery('#genooOverlay')[0]
        ),
        '.genooPop': Customizer.inlineStyleToObject(
            jQuery('.genooPop')[0]
        ),

        'span.req': jQuery.extend(Customizer.inlineStyleToObject(
            jQuery('span.req')[0]
        ), (function () {
            if (window.getComputedStyle(jQuery('span.req')[0])['opacity'] != '1') {
                return {'display': 'none'};
            }
        })()),

        '.genooPop .genooPopLeft': {'width': 'auto'},
        '.genooModal .genooPopImage': Customizer.inlineStyleToObject(
            jQuery('.genooModal .genooPopImage')[0]
        ),
        '.genooModal .genooPopImage img': Customizer.inlineStyleToObject(
            jQuery('.genooModal .genooPopImage img')[0]
        ),

        '.genooModal .progress': Customizer.inlineStyleToObject(
            jQuery('.genooPop .progress')[0]
        ),
        '.genooModal .progress .progress-bar': Customizer.inlineStyleToObject(
            jQuery('.genooPop .progress .progress-bar')[0]
        ),
        '.genooModal .progress .progress-per': Customizer.inlineStyleToObject(
            jQuery('.genooPop .progress .progress-per')[0]
        ),
        '.genooModal .genooCountdownText p': Customizer.inlineStyleToObject(
            jQuery('.genooModal .genooCountdownText p')[0]
        ),
        '.genooModal .timing': Customizer.inlineStyleToObject(
            jQuery('.genooModal .timing')[0]
        ),
        '.genooModal .timing span': Customizer.inlineStyleToObject(
            jQuery('.genooModal .timing span')[0]
        ),

        '.genooModal .genooPopTitle': Customizer.inlineStyleToObject(
            jQuery('.genooModal .genooPopTitle')[0]
        ),
        '.genooModal .genooPopTitle p': Customizer.inlineStyleToObject(
            jQuery('.genooModal .genooPopTitle p')[0]
        ),

        '.genooModal .genooPopIntro:not(.genooPopTitle)': Customizer.inlineStyleToObject(
            jQuery('.genooModal .genooPopIntro:not(.genooPopTitle)')[0]
        ),
        '.genooModal .genooPopIntro:not(.genooPopTitle) p': Customizer.inlineStyleToObject(
            jQuery('.genooModal .genooPopIntro:not(.genooPopTitle) p')[0]
        ),

        '.genooModal label': Customizer.inlineStyleToObject(
            jQuery('.genooModal label')[0]
        ),

        '::-webkit-input-placeholder': Customizer.inlineStyleToObject(
            document.getElementById('input-placeholder-styles')
        ),
        ':-ms-input-placeholder': Customizer.inlineStyleToObject(
            document.getElementById('input-placeholder-styles')
        ),
        '::-moz-placeholder': Customizer.inlineStyleToObject(
            document.getElementById('input-placeholder-styles')
        ),
        ':-moz-placeholder': Customizer.inlineStyleToObject(
            document.getElementById('input-placeholder-styles')
        ),

        '.genooModal input[type="text"], .genooModal input[type="date"], .genooModal input[type="datetime"], .genooModal input[type="datetime-local"], .genooModal input[type="email"], .genooModal input[type="month"], .genooModal input[type="number"], .genooModal input[type="password"], .genooModal input[type="search"], .genooModal input[type="tel"], .genooModal input[type="time"], .genooModal input[type="url"], .genooModal input[type="week"]': Customizer.inlineStyleToObject(
            jQuery('.genooModal input[type="text"]')[0]
        ),
        '.genooModal dropdown': Customizer.inlineStyleToObject(
            jQuery('.genooModal select')[0]
        ),
        '.genooModal textarea': Customizer.inlineStyleToObject(
            jQuery('.genooModal input[type="text"]')[0]
        ),
        '.genooModal input[type="submit"]': Customizer.inlineStyleToObject(
            jQuery('.genooModal input[type="submit"]')[0]
        ),

        '.genooModal input[type="submit"]:hover': {
            'background-color': document.querySelector('.genooModal input[type="submit"]').style['hover-background-color'],
            'color': document.querySelector('.genooModal input[type="submit"]').style['hover-color']
        },

        '.genooModal .genooPopFooter': Customizer.inlineStyleToObject(
            jQuery('.genooModal .genooPopFooter')[0]
        ),

        '.genooModal input[type="radio"]': Customizer.inlineStyleToObject(
            jQuery('.genooModal input[type="radio"]')[0]
        ),
        '.genooModal input[type="radio"]:checked': Customizer.inlineStyleToObject(
            jQuery('.genooModal input[type="radio"]:checked')[0]
        ),
        '.genooModal input[type="checkbox"]': Customizer.inlineStyleToObject(
            jQuery('.genooModal input[type="checkbox"]')[0]
        ),
        '.genooModal input[type="checkbox"]:checked': Customizer.inlineStyleToObject(
            jQuery('.genooModal input[type="checkbox"]:checked')[0]
        )
    };
    return styles;
};


var modalSettings = new attributeForm({
    attributes: [
        {
            target: 'border-radius',
            type: 'number',
            label: 'Border Radius',
            returns: '%val%px'
        },
        {
            target: 'background-color',
            type: 'color',
            label: 'Background Color',
            returns: '%val%'
        },
        {
            target: 'padding',
            type: 'number',
            label: 'Padding',
            returns: '%val%px'
        },
    ]
});

var reqSettings = new attributeForm({
    attributes: [
        {
            target: 'opacity',
            type: 'toggle',
            label: 'Show required text?',
            returns: {
                true: '1',
                false: '0.3'
            }
        }
    ]
});

var imageSettings = new attributeForm({
    attributes: [
        {
            target: 'width',
            type: 'number',
            label: 'Column Width',
            min: '0',
            max: '360',
            step: '5',
            returns: '%val%px'
        },
        {
            target: ['children', '0', 'style', 'width'],
            type: 'number',
            label: 'Image Width',
            min: '0',
            step: '5',
            returns: '%val%px'
        },
        {
            target: 'text-align',
            type: 'dropdown',
            label: 'Align',
            emptyState: 'Choose an option',
            options: [
                {name: 'Left', value: 'left'},
                {name: 'Center', value: 'center'},
                {name: 'Right', value: 'right'}
            ]
        },
        {
            target: 'margin-top',
            type: 'number',
            label: 'Top Spacing',
            returns: '%val%px'
        }
    ]
});

var fontStack = [
    {name: 'Impact', value: 'Impact, Charcoal, sans-serif'},
    {name: 'Palatino Linotype', value: '\'Palatino Linotype\', \'Book Antiqua\', Palatino, serif'},
    {name: 'Tahoma', value: 'Tahoma, Geneva, sans-serif'},
    {name: 'Century Gothic', value: 'Century Gothic, sans-serif'},
    {name: 'Lucida Sans Unicode', value: '\'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif;'},
    {name: 'Arial Black', value: '\'Arial Black\', Gadget, sans-serif'},
    {name: 'Times New Roman', value: '\'Times New Roman\', Times, serif'},
    {name: 'Arial Narrow', value: '\'Arial Narrow\', sans-serif'},
    {name: 'Verdana', value: 'Verdana, Geneva, sans-serif'},
    {name: 'Copperplate Gothic Light', value: ' Copperplate, Copperplate Gothic Light, sans-serif'},
    {name: 'Lucida Console', value: '\'Lucida Console\', Monaco, monospace'},
    {name: 'Gill Sans', value: ' Gill Sans, Gill Sans MT, sans-serif'},	// NOTE
    {name: 'Trebuchet MS', value: '\'Trebuchet MS\', Helvetica, sans-serif'},
    {name: 'Courier New', value: '\'Courier New\', Courier, monospace'},
    {name: 'Arial', value: 'Arial, Helvetica, sans-serif'},
    {name: 'Georgia', value: 'Georgia, Serif'},
];

var textSettings = new attributeForm({
    attributes: [
        {
            target: 'color',
            type: 'color',
            label: 'Font Color',
            returns: '%val%'
        },
        {
            target: 'font-size',
            type: 'number',
            placeholder: '16px',
            label: 'Font size',
            min: 6,
            returns: '%val%px'
        },
        {
            target: 'font-family',
            type: 'dropdown',
            label: 'Font Family',
            emptyState: 'Choose an option',
            options: fontStack
        },
        {
            target: 'font-weight',
            type: 'dropdown',
            label: 'Font Weight',
            emptyState: 'Choose an option',
            options: [
                {name: 'Bold', value: '900'},
                {name: 'Regular', value: 'normal'},
                {name: 'Thin', value: '100'}
            ]
        }
    ]
});

var progressBarSettings = new attributeForm({
    attributes: [
        {
            target: 'background-color',
            type: 'color',
            label: 'Bar Background',
            returns: '%val%'
        },
        {
            target: ['children', '0', 'style', 'background-color'],
            type: 'color',
            label: 'Progress Background Color',
            returns: '%val%'
        },
        {
            target: 'color',
            type: 'color',
            label: 'Font Color',
            returns: '%val%'
        },
        {
            target: ['children', '1', 'style', 'font-size'],
            type: 'number',
            placeholder: '16px',
            label: 'Font size',
            min: 6,
            returns: '%val%px'
        },
        {
            target: ['children', '1', 'style', 'font-family'],
            type: 'dropdown',
            label: 'Font Family',
            emptyState: 'Choose an option',
            options: fontStack
        },
        {
            target: ['children', '1', 'style', 'font-weight'],
            type: 'dropdown',
            label: 'Font Weight',
            emptyState: 'Choose an option',
            options: [
                {name: 'Bold', value: '900'},
                {name: 'Regular', value: 'normal'},
                {name: 'Thin', value: '100'}
            ]
        },
        {
            target: 'line-height',
            type: 'number',
            placeholder: '20px',
            label: 'Height',
            returns: '%val%px'
        }
    ]
});

var buttonSettings = new attributeForm({
    attributes: [
        // NOTE: Align cannot be added because button's parents do not have a unique
        //       selector.
        // {
        // 	target: ['parentElement', 'style', 'text-align'],
        // 	type: 'dropdown',
        // 	label: 'Align',
        // 	emptyState: 'Choose an option',
        // 	options: [
        // 		{name: 'Left', value: 'left'},
        // 		{name: 'Center', value: 'center'},
        // 		{name: 'Right', value: 'right'}
        // 	]
        // },
        // Font Color
        {
            target: 'color',
            type: 'color',
            label: 'Font Color',
            returns: '%val%'
        },
        // Hover Font Color
        {
            target: 'hover-color',
            type: 'color',
            label: 'Font Color on Hover',
            returns: '%val%'
        },
        // Background Color
        {
            target: 'background-color',
            type: 'color',
            label: 'Button Color',
            returns: '%val%'
        },
        {
            target: 'hover-background-color',
            type: 'color',
            label: 'Button Color on Hover',
            returns: '%val%'
        },
        {
            target: 'font-size',
            type: 'number',
            placeholder: '16px',
            label: 'Font size',
            min: 6,
            returns: '%val%px'
        },
        {
            target: 'font-family',
            type: 'dropdown',
            label: 'Font Family',
            emptyState: 'Choose an option',
            options: fontStack
        },
        {
            target: 'font-weight',
            type: 'dropdown',
            label: 'Font Weight',
            emptyState: 'Choose an option',
            options: [
                {name: 'Bold', value: '900'},
                {name: 'Regular', value: 'normal'},
                {name: 'Thin', value: '100'}
            ]
        },
        // Hover Background Color
        // Border Style
        {
            target: 'border-style',
            type: 'dropdown',
            label: 'Border Style',
            emptyState: 'Choose an option',
            options: [
                {name: 'Solid', value: 'solid'},
                {name: 'Dashed', value: 'dashed'},
                {name: 'Invisible', value: 'hidden'}
            ]
        },
        {
            target: 'border-width',
            type: 'number',
            label: 'Border Width',
            returns: '%val%px'
        },
        {
            target: 'border-color',
            type: 'color',
            label: 'Border Color',
            returns: '%val%'
        },
        {
            target: 'border-radius',
            type: 'number',
            placeholder: '4px',
            label: 'Border Radius',
            returns: '%val%px'
        },
        // Text Padding
        {
            target: 'padding',
            type: 'number',
            label: 'Text Padding',
            returns: '%val%px'
        },
    ]
});

var countdownCounterSettings = new attributeForm({
    attributes: [
        {
            target: 'background-color',
            type: 'color',
            label: 'Box Color',
            returns: '%val%'
        },
        {
            target: 'color',
            type: 'color',
            label: 'Number Color',
            returns: '%val%'
        },
        {
            target: ['parentElement', 'style', 'color'],
            type: 'color',
            label: 'Label Color',
            returns: '%val%'
        },
        {
            target: ['parentElement', 'style', 'line-height'],
            type: 'number',
            placeholder: '4px',
            label: 'Spacing',
            returns: '%val%px'
        },
        {
            target: 'border-radius',
            type: 'number',
            placeholder: '4px',
            label: 'Number Border Radius',
            returns: '%val%px'
        },
        {
            target: 'padding',
            type: 'number',
            placeholder: '4px',
            label: 'Number Padding',
            returns: '%val%px'
        },

        // ---


        {
            target: ['parentElement', 'style', 'font-size'],
            type: 'number',
            placeholder: '16px',
            label: 'Font size',
            returns: '%val%px'
        },
        {
            target: ['parentElement', 'style', 'font-family'],
            type: 'dropdown',
            label: 'Font Family',
            emptyState: 'Choose an option',
            options: fontStack
        },
        {
            target: ['parentElement', 'style', 'font-weight'],
            type: 'dropdown',
            label: 'Font Weight',
            emptyState: 'Choose an option',
            options: [
                {name: 'Bold', value: '900'},
                {name: 'Regular', value: 'normal'},
                {name: 'Thin', value: '100'}
            ]
        }
    ]
});

var placeholderSettings = new attributeForm({
    attributes: [
        {
            target: 'color',
            type: 'color',
            label: 'Placholder Color',
            returns: '%val%'
        }
    ]
});

var textInputSettings = new attributeForm({
    attributes: [
        {
            target: 'background-color',
            type: 'color',
            label: 'Background Color',
            returns: '%val%'
        },
        {
            target: 'border-style',
            type: 'dropdown',
            label: 'Border Style',
            emptyState: 'Choose an option',
            options: [
                {name: 'Solid', value: 'solid'},
                {name: 'Dashed', value: 'dashed'},
                {name: 'Invisible', value: 'hidden'}
            ]
        },
        {
            target: 'border-width',
            type: 'number',
            label: 'Border Width',
            returns: '%val%px'
        },
        {
            target: 'border-color',
            type: 'color',
            label: 'Border Color',
            returns: '%val%'
        },
        {
            target: 'border-radius',
            type: 'number',
            placeholder: '4px',
            label: 'Border Radius',
            returns: '%val%px'
        },
        {
            target: 'padding',
            type: 'number',
            placeholder: '4px',
            label: 'Text Padding',
            returns: '%val%px'
        },

    ]
});

var dropDownSettings = new attributeForm({
    attributes: [
        {
            target: ['parentElement', 'style', 'text-align'],
            type: 'dropdown',
            label: 'Align',
            emptyState: 'Choose an option',
            options: [
                {name: 'Left', value: 'left'},
                {name: 'Center', value: 'center'},
                {name: 'Right', value: 'right'}
            ]
        },
    ]
});

var radioSettings = new attributeForm({
    attributes: [
        {
            target: 'background-color',
            type: 'color',
            label: 'Background Color',
            returns: '%val%'
        },
        {
            target: 'border-style',
            type: 'dropdown',
            label: 'Border Style',
            emptyState: 'Choose an option',
            options: [
                {name: 'Solid', value: 'solid'},
                {name: 'Dashed', value: 'dashed'},
                {name: 'Invisible', value: 'hidden'}
            ]
        },
        {
            target: 'border-width',
            type: 'number',
            label: 'Border Width',
            returns: '%val%px'
        },
        {
            target: 'border-color',
            type: 'color',
            label: 'Border Color',
            returns: '%val%'
        },
        {
            target: 'border-radius',
            type: 'number',
            placeholder: '4px',
            label: 'Border Radius',
            returns: '%val%px'
        }
    ]
});
var radioSettings_checked = new attributeForm({
    heading: 'Checked Style',
    attributes: [
        {
            target: 'background-color',
            type: 'color',
            label: 'Background Color',
            returns: '%val%'
        },
        {
            target: 'border-style',
            type: 'dropdown',
            label: 'Border Style',
            emptyState: 'Choose an option',
            options: [
                {name: 'Solid', value: 'solid'},
                {name: 'Dashed', value: 'dashed'},
                {name: 'Invisible', value: 'hidden'}
            ]
        },
        {
            target: 'border-width',
            type: 'number',
            label: 'Border Width',
            returns: '%val%px'
        },
        {
            target: 'border-color',
            type: 'color',
            label: 'Border Color',
            returns: '%val%'
        },
        {
            target: 'border-radius',
            type: 'number',
            placeholder: '4px',
            label: 'Border Radius',
            returns: '%val%px'
        }
    ]
});

var checkboxSettings = new attributeForm({
    attributes: [
        {
            target: 'background-color',
            type: 'color',
            label: 'Background Color',
            returns: '%val%'
        },
        {
            target: 'border-style',
            type: 'dropdown',
            label: 'Border Style',
            emptyState: 'Choose an option',
            options: [
                {name: 'Solid', value: 'solid'},
                {name: 'Dashed', value: 'dashed'},
                {name: 'Invisible', value: 'hidden'}
            ]
        },
        {
            target: 'border-width',
            type: 'number',
            label: 'Border Width',
            returns: '%val%px'
        },
        {
            target: 'border-color',
            type: 'color',
            label: 'Border Color',
            returns: '%val%'
        },
        {
            target: 'border-radius',
            type: 'number',
            placeholder: '4px',
            label: 'Border Radius',
            returns: '%val%px'
        }
    ]
});
var checkboxSettings_checked = new attributeForm({
    heading: 'Checked Style',
    attributes: [
        {
            target: 'background-color',
            type: 'color',
            label: 'Background Color',
            returns: '%val%'
        },
        {
            target: 'border-style',
            type: 'dropdown',
            label: 'Border Style',
            emptyState: 'Choose an option',
            options: [
                {name: 'Solid', value: 'solid'},
                {name: 'Dashed', value: 'dashed'},
                {name: 'Invisible', value: 'hidden'}
            ]
        },
        {
            target: 'border-width',
            type: 'number',
            label: 'Border Width',
            returns: '%val%px'
        },
        {
            target: 'border-color',
            type: 'color',
            label: 'Border Color',
            returns: '%val%'
        },
        {
            target: 'border-radius',
            type: 'number',
            placeholder: '4px',
            label: 'Border Radius',
            returns: '%val%px'
        }
    ]
});

//
// Attach an event to every item inside of the popup (disambiguation)
//

jQuery(document).ready(function ($) {

    // Attach color picker
    jQuery('.genoo-color-picker').wpColorPicker({change: Customizer.colorPicker});

    // Joshuas code
    var updateEvents = 'hover keyup change';

    /**
     * @param editorElem  the node you're adding the form to
     * @param editorObject the instance of attributeForm being added
     */
    function attachEditorToObject(editorElem, editorObject, callBack) {
        editorObject.appendTo(editorElem);
        jQuery(editorElem).on(
            updateEvents,
            function () {
                editorObject.update();
                if (callBack) {
                    callBack(editorObject.attachedObject);
                }
            }
        );
    }

    attachEditorToObject(
        document.getElementById('button-editor'),
        buttonSettings
    );

    attachEditorToObject(
        document.getElementById('image-editor'),
        imageSettings
    );
    attachEditorToObject(
        document.getElementById('modal-editor'),
        modalSettings
    );
    attachEditorToObject(
        document.getElementById('text-editor'),
        textSettings
    );
    attachEditorToObject(
        document.getElementById('req-editor'),
        reqSettings
    );
    attachEditorToObject(
        document.getElementById('countdown-counter-editor'),
        countdownCounterSettings
    );
    attachEditorToObject(
        document.getElementById('progress-bar-editor'),
        progressBarSettings
    );
    attachEditorToObject(
        document.getElementById('text-input-editor'),
        textInputSettings
    );
    attachEditorToObject(
        document.getElementById('text-input-editor'),
        placeholderSettings
    );

    attachEditorToObject(
        document.getElementById('drop-down-editor'),
        dropDownSettings
    );

    radioSettings.appendTo(jQuery('#radio-editor')[0]);
    radioSettings_checked.appendTo(jQuery('#radio-editor')[0]);
    jQuery('#radio-editor, [type="radio"]').on(
        updateEvents,
        function () {
            jQuery('[type="radio"]').each(
                function () {
                    radioSettings.applyStyles(this);
                    if (this.checked) {
                        // Apply the checked checkbox styles
                        radioSettings_checked.applyStyles(this);
                    }
                }
            );
        }
    );

    checkboxSettings.appendTo(jQuery('#checkbox-editor')[0]);
    checkboxSettings_checked.appendTo(jQuery('#checkbox-editor')[0]);
    jQuery('#checkbox-editor, [type="checkbox"]').on(
        updateEvents,
        function () {
            jQuery('[type="checkbox"]').each(
                function () {
                    checkboxSettings.applyStyles(this);
                    if (this.checked) {
                        // Apply the checked checkbox styles
                        checkboxSettings_checked.applyStyles(this);
                    }
                }
            );
        }
    );

    // Add style tag to edit for placeholders in the header
    try {
        var placeholderStyleElem = document.createElement('style');
        placeholderStyleElem.id = 'input-placeholder-styles';
        var placeholderStyleSheet =
            '::-webkit-input-placeholder {;' +
            'color: %color%;' +
            '}' +
            ':-ms-input-placeholder {' +
            'color: %color%;' +
            '}' +
            '::-moz-placeholder {' +
            'color: %color%;' +
            '}' +
            ':-moz-placeholder {' +
            'color: %color%;' +
            '}';

        var savedJSON = JSON.parse(
            document.querySelector('#content-textarea').innerHTML
        );

        var startingColor;
        if (savedJSON.hasOwnProperty('::-webkit-input-placeholder')) {
            if (savedJSON['::-webkit-input-placeholder'].color) {
                startingColor = savedJSON['::-webkit-input-placeholder'].color;
            }
        } else {
            startingColor = "#cccccc";
        }
        placeholderStyleElem.style.color = startingColor;
        placeholderStyleElem.innerHTML = placeholderStyleSheet
            .replace(/%color%/gi, startingColor);
        document.head.appendChild(
            placeholderStyleElem
        );
        // We're attaching this to the style tag so that we have something to read
        // from.
        placeholderSettings.attach(placeholderStyleElem);
    } catch(Error){
        // We dont care at this moment
    }


    // Things to check for whenever an update event is fired
    $('#editor-wrapper').on(
        updateEvents,
        function () {

            //
            // Compare the top offset of all of the countdown timers. If they have a
            // difference greater than the threshold, make them wrap
            //

            // Get the top offsets to compare
            var topOffsets = [];
            $('.timing span').css('display', 'initial')
                .parent().each(
                function () {
                    topOffsets.push(
                        $(this).offset().top
                    );
                }
            );

            // Look out for future me (or whoever is reading this, Hi!)
            if (!topOffsets[0]) {
                console.warn('this script was designed to handle countdown timer wrapping, please delete to prevent errors');
            }

            // Compare each countdown's top offset to the first one.
            var forceWrap = false;
            var threshold = 1;
            for (var i = 0; i < topOffsets.length; i++) {
                if (topOffsets[0] - topOffsets[i] > threshold ||
                    topOffsets[i] - topOffsets[0] > threshold) {
                    forceWrap = true;
                }
            }

            // Wrap 'em!
            if (forceWrap) {
                $('.timing span').css('display', 'block');
            } else {
                $('.timing span').css('display', 'initial');
            }

            //
            // Adding placeholders to the top
            //

            var placeholderColor = placeholderStyleElem.style.color;
            placeholderStyleElem.innerHTML = placeholderStyleSheet
                .replace(/%color%/gi, placeholderColor);


            // Resize the modal box when the image is resized
            // TODO: add left margin into calculation
            var columnWidth = document.querySelector('.genooPopImage').style.width;
            var modalWidth = ( parseInt(columnWidth) + 345 ) + 'px';
            var currentModal = document.querySelector('#modalWindowGenoodynamiccta1');
            var currentModalWidth = currentModal.style.width;
            var isSmallScreen = parseInt(
                    window.getComputedStyle(document.body).width
                ) < 1150;

            if (columnWidth != modalWidth && !isSmallScreen) {
                currentModal.style.width = modalWidth;
                currentModal.style['margin-left'] = -( parseInt(modalWidth) / 2 ) + 'px';
            }

            if (isSmallScreen) {
                currentModal.removeAttribute('style');
            }

        }
    );


    // Attach the psuedo-editors to ghost elements that we can get stuff from
    // later
    // radioSettings_checked.update();

    // The progress bar's background color is set to rgba(0,0,0,0), however,
    // this color picker doesnt do transparency. So it just comes of as black
    // (instead of transparent). Make it start out as white.
    // (NOTE: assumes only one progress bar exists.)
    document.querySelectorAll('.genooPopProgress > .progress')[0].style.backgroundColor = '#ffffff';

    // Start out with all of the options hidden
    jQuery('#editor-wrapper *[id$="editor"]').hide();

    // Add events for selecting the different items
    jQuery('body .genooPop').find(
        '.progress, .genooCountdownText p, .genooPopFooter p, .genooPopIntro p,' +
        ' .timing, label, .genooPopImage,  ' +
        'input[type="text"], select, textarea, input[type="submit"], .req'
    ).addClass('hovered-item').on(
        'click',
        function (event) {
            // Prevent any non-selecting actions from happening after clicking
            // if this is not a checkbox
            if (!jQuery(this).is(jQuery('input[type="radio"], input[type="checkbox"]').parent())) {
                event.preventDefault();
            }

            var type;

            if (jQuery(this).is('.progress')) {
                type = 'Progress Bar';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                // jQuery("#text-editor").show();
                jQuery("#progress-bar-editor").show();
                // textSettings.attach(jQuery(this).find('.progress-per')[0]);
                progressBarSettings.attach(this);
            }
            // --
            // if (jQuery(this).is(jQuery('p').not(
            // 		jQuery('label, input, textarea').parent(), jQuery('.genooPopIntro').children()))
            // ) {
            // 	type = 'Paragraph';
            // 	jQuery('#editor-wrapper *[id$="editor"]').hide()
            // 	jQuery("#text-editor").show();
            // 	textSettings.attach(this);
            // }

            if (jQuery(this).is(
                    jQuery('.genooPopIntro').not('.genooPopTitle').find('p'))
            ) {
                type = 'Paragraph';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                jQuery("#text-editor").show();
                textSettings.attach(
                    document.querySelectorAll('.genooPop .genooPopIntro:not(.genooPopTitle) p')
                );
            }

            if (jQuery(this).is(jQuery('.genooPopTitle p'))
            ) {
                type = 'Title';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                jQuery("#text-editor").show();
                textSettings.attach(
                    document.querySelectorAll('.genooPop .genooPopTitle p')
                );
            }

            if (jQuery(this).is(jQuery('.genooPopFooter p'))
            ) {
                type = 'Footer Text';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                jQuery("#text-editor").show();
                textSettings.attach(
                    document.querySelectorAll('.genooPop .genooPopFooter p')
                );
            }

            if (jQuery(this).is(jQuery('span.req'))
            ) {
                type = 'Required text';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                jQuery("#text-editor, #req-editor").show();
                textSettings.attach(document.querySelectorAll('.req'));
                reqSettings.attach(document.querySelectorAll('.req'))
            }

            if (jQuery(this).is('.genooCountdownText p')) {
                type = 'Countdown Label';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                jQuery("#text-editor").show();
                textSettings.attach(
                    document.querySelectorAll('.genooCountdownText p'));
            }

            if (jQuery(this).is('.timing')) {
                type = 'Countdown';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                jQuery("#countdown-counter-editor").show();
                countdownCounterSettings.attach(
                    document.querySelectorAll('.genooPop .timing span'));
            }

            if (jQuery(this).is('.genooPopImage')) {
                type = 'Image';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                jQuery("#image-editor").show();
                imageSettings.attach(
                    document.querySelectorAll('.genooPopImage')
                );
            }

            if (jQuery(this).is('label')) {
                type = 'Label';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                jQuery("#text-editor")[0].style.display = 'block';
                textSettings.attach(document.querySelectorAll('.genooPop label'));
            }

            if (jQuery(this).is('input[type="text"], textarea')) {
                type = 'Text input';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                var input = document.querySelectorAll(
                    '.genooPop input[type="text"], .genooPop textarea'
                );
                textSettings.attach(input);
                textInputSettings.attach(input);
                jQuery("#text-editor, #text-input-editor").show();
            }

            if (jQuery(this).is('select')) {
                type = 'Dropdown';
                // Close the dropdown so that you can see the styles being appllied
                this.blur();
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                input = document.querySelectorAll('.genooPop select');
                // textSettings.attach(input);
                dropDownSettings.attach(input);
                // textInputSettings.attach(input);
                jQuery("#drop-down-editor").show();
            }

            if (jQuery(this).is('input[type="submit"]')) {
                type = 'Button';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                buttonSettings.attach(this);
                jQuery("#button-editor").show();
            }

            // Radio Buttons
            var radioList = document.querySelectorAll('input[type="radio"]');
            if (jQuery(this).is(jQuery(radioList).parent())) {
                type = 'Radio Button';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                jQuery("#radio-editor").show();
            }

            // Checkbox Buttons
            var checkboxList = document.querySelectorAll('input[type="checkbox"]');
            if (jQuery(this).is(jQuery(checkboxList).parent())) {
                type = 'Checkbox Button';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                jQuery("#checkbox-editor").show();
            }

            jQuery('#selectedElem').text(type);

        }
    );

    jQuery('#genooOverlay, .genooModal').click(
        function (event) {
            if (jQuery(this).is(event.target)) {
                var type = 'Modal Settings';
                jQuery('#editor-wrapper *[id$="editor"]').hide();
                jQuery("#modal-editor").show();
                modalSettings.attach(jQuery('.genooPop')[0]);
                jQuery('#selectedElem').text(type);
            }
        }
    );

    // Upon saving
    jQuery('#post').submit(function () {
        // Get data
        var data = Customizer.update();
        // Set textarae data
        document.getElementById('content-textarea').value = JSON.stringify(data);
        document.getElementById('content-textarea').innerHTML = JSON.stringify(data);
    });

    // Load styles upon load
    Customizer.onLoad();

    // Stop form submisson
    jQuery('#styler_applied-style :input').on('keyup keypress', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });


    // Enterperate style properties that match hover-XYZ as XYZ when you hover

    jQuery('[data-hover-style]').on(
        'hover',
        function () {
            // Add the styles to the end of the style attribute
            var currStyle = jQuery(this).attr('style');
            // var addStyle = jQuery(this).attr('data-hover-style');
            var hoverStyles = '';
            var styles = this.style;
            var property;
            for (property in styles) {
                if (property.match(/^hover-/)) {
                    var propertyName = property.replace(/^hover-/, '');
                    hoverStyles += propertyName + ':' + styles[property] + ';';
                }
            }

            var addStyle = '/*HOVER*/' + hoverStyles;
            jQuery(this).attr('style', currStyle + addStyle);
        }
    ).on(
        'mousedown mouseout',
        function () {
            // Remove the styles from end of style attribute
            var currStyle = jQuery(this).attr('style');
            this.setAttribute(
                'data-hover-style',
                currStyle.replace(/^.*\/\*HOVER\*\/(.*)/gi, '$1')
            );
            jQuery(this).attr('style', currStyle.replace(/\/\*HOVER\*\/.*/gi, ''));
        }
    );

    // Loop though the post content and apply the hover styles
    // var postContent = JSON.parse(document.querySelector('#content-textarea').value);
    // var matchHoverPsuedo = /:hover$/;
    // var rule;
    // for (rule in postContent){
    //     if (rule.match(matchHoverPsuedo)) {
    //         var cssRules = postContent[rule];
    //
    //         var $hoverElem = $(rule.replace(matchHoverPsuedo, ''));
    //         $hoverElem.attr(
    //             'data-hover-style', ''
    //         );
    //
    //         jQuery(rule.replace(matchHoverPsuedo, '')).each(function(){
    //             var property;
    //             for (property in cssRules) {
    //                 this.style['hover-' + property] = cssRules[property];
    //             }
    //         });
    //     }
    // }

});

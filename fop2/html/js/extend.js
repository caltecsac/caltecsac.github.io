/*
* A Popup class to open new windows, using the Prototype.js lib.
*
* Basic Usage:
*        var win = new Popup.Base( url_for_popup [, options [, window_options ] ] )
*
* Example:
*        var win = new Popup.Base('http://www.google.com/');
* With options:
*   var win = new Popup.Base('http://www.google.com/', { focus: true }, { statusbar: 1});
*
* To close the window:
*        win.close();
*
* To focus the window:
*        win.focus();
*/

var Popup = Object.extend(Object.extend(Enumerable), {
});

Popup.Base = Class.create();
Popup.Base.prototype = {
    // open a popup
    // only mandatory is the url for the popup
    initialize: function() {
        this.url = arguments[0];
        // get the options
        this.options = Object.extend({
            name: 'popup',
            focus: false
        }, arguments[1] || {});

        // get the window options
        this.window_options = $H(Object.extend({
            width: 640,
            height: 480,
            toolbar: 0,
            scrollbars: 1,
            location: 0,
            statusbar: 0,
            menubar: 0,
            resizable: 1
        }, arguments[2] || {}));

        this.window = window.open(this.url, this.options.name, this.window_options.map(function(v, i) {
            return v[0] + '=' + v[1];
        }).join(', '));
        Popup[this.url] = this; // place in the popup object, to be able to iterate over it

        if (this.options.focus) {
            this.focus();
        }
    },

    // close a popup window
    close: function() {
        this.window.close();
        if (this.url in Popup) { // remove from the popup object
            delete Popup[this.url];
        }
    },

    // focus an open popup window
    focus: function() {
        this.window.focus();
    }
};

// Introduces Event delegation (http://icant.co.uk/sandbox/eventdelegation)
Object.extend(Event, {
  delegate: function(element, eventName, targetSelector, handler) {
    var element = $(element);

    function selectorMatch(element) {
      return element.match(targetSelector);
    }

    function validateTarget(origin) {
      if ( origin.match(targetSelector) ) { return origin; }
      var ancestors = origin.ancestors();
      return ancestors.find(selectorMatch);
    }

    function createDelegation(_delegatedEvent) {
      var rawOrigin = _delegatedEvent.element();
      var origin = validateTarget(rawOrigin);
      if ( origin != null && (typeof handler == 'function') ){ 
        _delegatedEvent.element = function() { return origin; }
        return handler(_delegatedEvent);
      }
    };

    element.observe(eventName, createDelegation);
    return element;
  },

  delegators: function(element, eventName, rules) {
    var element = $(element);
    function delegateRule(rule) {
      element.delegate(eventName, rule.key, rule.value)
    }
    $H(rules).each(delegateRule)
    return element;
  }
})

Element.addMethods({
  delegate: Event.delegate,
  delegators: Event.delegators
})

Object.extend(document, {
  delegate: Event.delegate,
  delegators: Event.delegators
})



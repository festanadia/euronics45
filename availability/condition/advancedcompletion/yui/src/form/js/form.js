/**
 * JavaScript for form editing completion conditions.
 *
 * @module moodle-availability_advancedcompletion-form
 */
M.availability_advancedcompletion = M.availability_advancedcompletion || {};

/**
 * @class M.availability_advancedcompletion.form
 * @extends M.core_availability.plugin
 */
M.availability_advancedcompletion.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} cms Array of objects containing cmid => name
 */
M.availability_advancedcompletion.form.initInner = function(html,defaultTime,cms) {
    this.cms = cms;
    this.html = html;
    this.defaultTime = defaultTime;
};

M.availability_advancedcompletion.form.getNode = function(json) {
    // Create HTML structure.
    var strings = M.str.availability_advancedcompletion;
    //console.log('HTML='+ this.html);

    var html = strings.title + ' <span class="availability-group"><label>' +
            '<span class="accesshide">' + strings.label_cm + ' </span>' +
            '<select name="cm" title="' + strings.label_cm + '">' +
            '<option value="0">' + M.str.moodle.choosedots + '</option>';
    for (var i = 0; i < this.cms.length; i++) {
        var cm = this.cms[i];
        // String has already been escaped using format_string.
        html += '<option value="' + cm.id + '">' + cm.name + '</option>';
    }
    html += '</select></label> <label><span class="accesshide">' + strings.label_completion +
            ' </span><select name="e" title="' + strings.label_completion + '">' +
            '<option value="1">' + strings.option_complete + '</option>' +
            '<option value="0">' + strings.option_incomplete + '</option>' +
            '<option value="2">' + strings.option_pass + '</option>' +
            '<option value="3">' + strings.option_fail + '</option>' +
            '</select></label></span><br>';
    
    html += strings.direction_label + ' <span class="availability-group">' +
            '<label><span class="accesshide">' + strings.direction_label + ' </span>' +
            '<select name="direction">' +
            '<option value="not applied">' + strings.direction_none + '</option>' +
            '<option value="&gt;=">' + strings.direction_from + '</option>' +
            '<option value="&lt;">' + strings.direction_until + '</option>' +
            '</select></label></span> ' + this.html;
    var node = Y.Node.create('<span>' + html + '</span>');

    // Set initial values.
    if (json.cm !== undefined &&
            node.one('select[name=cm] > option[value=' + json.cm + ']')) {
        node.one('select[name=cm]').set('value', '' + json.cm);
    }
    if (json.e !== undefined) {
        node.one('select[name=e]').set('value', '' + json.e);
    }

    // Add event handlers (first time only).
    if (!M.availability_advancedcompletion.form.addedEvents) {
        M.availability_advancedcompletion.form.addedEvents = true;
        var root = Y.one('#fitem_id_availabilityconditionsjson');
        
        root.delegate('change', function() {
            // Whichever dropdown changed, just update the form.
            M.core_availability.form.update();
        }, '.availability_advancedcompletion select');
        
        root.delegate('change', function() {
            // For the direction, just update the form fields.
            M.core_availability.form.update();
        }, '.availability_advancedcompletion select[name=direction]');

        root.delegate('change', function() {
            // Update time using AJAX call from root node.
            M.availability_advancedcompletion.form.updateTime(this.ancestor('span.availability_advancedcompletion'));
        }, '.availability_advancedcompletion select:not([name=direction])');
    }
    
    
    // Set initial value if non-default.
    if (json.t !== undefined) {
        node.setData('time', json.t);
        /*
        node.all('select:not([name=direction])').each(function(select) {
            select.set('disabled', true);
        });*/

        var url = M.cfg.wwwroot + '/availability/condition/advancedcompletion/ajax.php?action=fromtime' +
            '&time=' + json.t;
        Y.io(url, { on : {
            success : function(id, response) {
                var fields = Y.JSON.parse(response.responseText);
                //console.log('fields='+fields);
                for (var field in fields) {
                    var select = node.one('select[name=x\\[' + field + '\\]]');
                    select.set('value', '' + fields[field]);
                    select.set('disabled', false);
                }
            },
            failure : function() {
                window.alert(M.str.availability_advancedcompletion.ajaxerror);
            }
        }});
    } else {
        // Set default time that corresponds to the HTML selectors.
        node.setData('time', this.defaultTime);
    }
    if (json.d !== undefined) {
        node.one('select[name=direction]').set('value', json.d);
    }


    if (node.one('a[href=#]')) {
        // Add the date selector magic.
        M.form.dateselector.init_single_date_selector(node);

        // This special handler detects when the date selector changes the year.
        var yearSelect = node.one('select[name=x\\[year\\]]');
        var oldSet = yearSelect.set;
        yearSelect.set = function(name, value) {
            oldSet.call(yearSelect, name, value);
            if (name === 'selectedIndex') {
                // Do this after timeout or the other fields haven't been set yet.
                setTimeout(function() {
                    M.availability_advancedcompletion.form.updateTime(node);
                }, 0);
            }
        };
    }

    return node;
};

M.availability_advancedcompletion.form.fillValue = function(value, node) {
    value.cm = parseInt(node.one('select[name=cm]').get('value'), 10);
    value.e = parseInt(node.one('select[name=e]').get('value'), 10);
    value.d = node.one('select[name=direction]').get('value');
    value.t = parseInt(node.getData('time'), 10);
};

M.availability_advancedcompletion.form.fillErrors = function(errors, node) {
    var cmid = parseInt(node.one('select[name=cm]').get('value'), 10);
    if (cmid === 0) {
        errors.push('availability_advancedcompletion:error_selectcmid');
    }
};

/**
 * Updates time from AJAX. Whenever the field values change, we recompute the
 * actual time via an AJAX request to Moodle.
 *
 * This will set the 'time' data on the node and then update the form, once it
 * gets an AJAX response.
 *
 * @method updateTime
 * @param {Y.Node} component Node for plugin controls
 */

M.availability_advancedcompletion.form.updateTime = function(node) {
    // After a change to the date/time we need to recompute the
    // actual time using AJAX because it depends on the user's
    // time zone and calendar options.
    var url = M.cfg.wwwroot + '/availability/condition/advancedcompletion/ajax.php?action=totime' +
            '&year=' + node.one('select[name=x\\[year\\]]').get('value') +
            '&month=' + node.one('select[name=x\\[month\\]]').get('value') +
            '&day=' + node.one('select[name=x\\[day\\]]').get('value') +
            '&hour=' + node.one('select[name=x\\[hour\\]]').get('value') +
            '&minute=' + node.one('select[name=x\\[minute\\]]').get('value');
    Y.io(url, { on : {
        success : function(id, response) {
            node.setData('time', response.responseText);
            M.core_availability.form.update();
        },
        failure : function() {
            window.alert(M.str.availability_advancedcompletion.ajaxerror);
        }
    }});
};


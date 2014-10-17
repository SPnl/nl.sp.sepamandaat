function SepaMandaat() {
}

SepaMandaat.prototype.contactElement = '#contact_1';
SepaMandaat.prototype.contactHiddenElement = 'input[name="contact_select_id[1]"]';

SepaMandaat.prototype.currentContactId = false;

SepaMandaat.prototype.contactId = false;

SepaMandaat.prototype.init = function(contactId) {
    this.contactId = contactId;
    this.initEventHandlers(this);
};

SepaMandaat.prototype.retrieveContactId = function() {
    if (cj(this.contactHiddenElement) && cj(this.contactHiddenElement).val()) {
        return cj(this.contactHiddenElement).val();
    }
    return this.contactId;
};

SepaMandaat.prototype.retrieveSepaMandatesForContact = function(contactId) {
    if (contactId === this.currentContactId) {
        return;
    }
    this.currentConatctId = contactId;

    cj('#mandaat_id').find('option').each(function(index) {
        if (cj(this).val() > 0) {
            cj(this).remove();
        }
    });
    if (contactId > 0) {
        CRM.api('SepaMandaat', 'get', {'contact_id': contactId}, {
            success: function(data) {
                cj.each(data.values, function(key, value) {
                    cj('#mandaat_id').append('<option value="' + value.id + '">' + value.mandaat_nr + '</option>');
                });
            }
        });
    }
};


SepaMandaat.prototype.initEventHandlers = function(ctx) {
    //init onchange handlers to change the iban options
    cj(this.contactElement).blur(function(e) {
        var contactId = this.retrieveContactId();
        this.retrieveSepaMandatesForContact(contactId);
    }.bind(this));
};

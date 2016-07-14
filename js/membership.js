function SepaMandaat_Membership() {
    SepaMandaat.call(this);
}

SepaMandaat_Membership.prototype = new SepaMandaat();
SepaMandaat_Membership.prototype.constructor = SepaMandaat_Membership;
SepaMandaat_Membership.prototype.super = new SepaMandaat();

SepaMandaat_Membership.prototype.contributionContactElement = '#contribution_contact_1';
SepaMandaat_Membership.prototype.contributionContactHiddenElement = '#soft_credit_contact_id';
SepaMandaat_Membership.prototype.contributionDifferentContactElement = '#is_different_contribution_contact';
SepaMandaat_Membership.prototype.membership_type = '#membership_type_id_1';

SepaMandaat_Membership.prototype.retrieveContactId = function() {
    if (cj(this.contributionDifferentContactElement).is(':checked')) {
        return cj(this.contributionContactHiddenElement).val();
    } else {
        return this.super.retrieveContactId.call(this);
    }

};

SepaMandaat_Membership.prototype.initEventHandlers = function(ctx) {
    this.super.initEventHandlers.call(this, ctx);

    cj(this.contributionContactElement).blur(function(e) {
        var contactId = this.retrieveContactId();
        this.retrieveSepaMandatesForContact(contactId);
    }.bind(this));

    cj(this.contributionContactHiddenElement).change(function(e) {
        var contactId = this.retrieveContactId();
        this.retrieveSepaMandatesForContact(contactId);
    }.bind(this));

    cj(this.contributionDifferentContactElement + ':checkbox').change(function(e) {
        if (!cj(this.contributionDifferentContactElement).is(':checked')) {
            var contactId = this.retrieveContactId();
            this.retrieveSepaMandatesForContact(contactId);
        } else {
            var contactId = this.retrieveContactId();
            this.retrieveSepaMandatesForContact(contactId);
        }
    }.bind(this));
    
};
function SepaMandaat_Membershiprenewal() {
    SepaMandaat.call(this);
}

SepaMandaat_Membershiprenewal.prototype = new SepaMandaat();
SepaMandaat_Membershiprenewal.prototype.constructor = SepaMandaat_Membershiprenewal;
SepaMandaat_Membershiprenewal.prototype.super = new SepaMandaat();

SepaMandaat_Membershiprenewal.prototype.contributionContactElement = '#contribution_contact_1';
SepaMandaat_Membershiprenewal.prototype.contributionContactHiddenElement = 'input[name="contribution_contact_select_id[1]"]';
SepaMandaat_Membershiprenewal.prototype.contributionDifferentContactElement = '#contribution_contact';
SepaMandaat_Membershiprenewal.prototype.membership_type = '#membership_type_id_1';

SepaMandaat_Membershiprenewal.prototype.retrieveContactId = function() {
    if (cj(this.contributionDifferentContactElement).is(':checked')) {
        return cj(this.contributionContactHiddenElement).val();
    } else {
        return this.super.retrieveContactId.call(this);
    }

};

SepaMandaat_Membershiprenewal.prototype.initEventHandlers = function(ctx) {
    this.super.initEventHandlers.call(this, ctx);

    cj(this.contributionContactElement).blur(function(e) {
        var contactId = cj(this.contributionContactHiddenElement).val();
        this.retrieveIbanAccountsForContact(contactId);
    }.bind(this));

    cj(this.contributionDifferentContactElement + ':checkbox').change(function(e) {
        if (!cj(this.contributionDifferentContactElement).is(':checked')) {
            cj(this.contributionContactElement).val('');
        }
        var contactId = this.retrieveContactId();
        this.retrieveIbanAccountsForContact(contactId);
    }.bind(this));
    
};
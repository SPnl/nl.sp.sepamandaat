{capture name="sepa_mandaat" assign="sepa_mandaat"}
<tr class="crm-sepa_mandaat-form-block-sepa_mandaat">
    <td class="label">{$form.mandaat_id.label}</td>
    <td>{$form.mandaat_id.html}</td>
</tr>
{/capture}

<script type="text/javascript">
{literal}
cj(function() {
    cj('tr.crm-member-membershiprenew-form-block-total_amount').after('{/literal}{$sepa_mandaat|escape:'javascript'}{literal}');
    cj('select#mandaat_id').change(function() {
        cj('input[data-crm-custom="Membership_SEPA_Mandaat:mandaat_id"]').val(cj('select#mandaat_id').val());
    });

    var sepa_membership = new SepaMandaat_Membershiprenewal();
    sepa_membership.init('{/literal}{$snippet.contact_id}{literal}');
    cj('select#mandaat_id').val(cj('input[data-crm-custom="Membership_SEPA_Mandaat:mandaat_id"]').val());
    cj('#Membership_SEPA_Mandaat').addClass('hiddenElement');
});
{/literal}
</script>
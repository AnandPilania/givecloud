<div class="modal fade modal-primary" id="add-method-from-vault">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fas fa-plus fa-fw"></i> Add Method From Vault
                </h4>
            </div>

            <form method="post" action="{{ route('backend.members.import_payment_method_from_vault', $member) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong><i class="fa fas fa-exclamation-circle"></i> Caution:</strong>
                        &nbsp;&nbsp;This action will create a payment method using the information stored in the customer vault.
                    </div>
                    <div class="form-group">
                        <label for="mergeaccount-member_id" class="control-label">
                            Vault ID:
                        </label>
                        <input type="tel" class="form-control" name="vault_id" value="">
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="set_as_default" value="1" checked>
                            Set as default payment method
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fas fa-plus fa-fw"></i> Add
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-primary" id="mergeAccount">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fas fa-code-fork fa fas fa-flip-vertical fa-fw"></i> Merge Supporter
                </h4>
            </div>

            <form class="form-horizontal" method="post" action="{{ $member->id ? route('backend.member.merge', $member) : '#' }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong><i class="fa fas fa-exclamation-circle"></i> Caution:</strong>
                        &nbsp;&nbsp;You are about to archive this supporter and merge all its associated data into another supporter.
                        <ul class="mt-2 ml-6 list-disc">
                            <li>This supporter will be merged and all of their information (name, email, billing, shipping, etc) will be deleted</li>
                            <li>The master supporter's information (name, email, billing, shipping, etc) will not be impacted</li>
                            <li>All of this supporter's contributions, sponsorships, recurring payments, past payments and payment methods will now belong to the master supporter below</li>
                        </ul>
                    </div>

                    <p>Choose the supporter you want to merge this master supporter into:</p>
                    <br>
                    <div class="form-group">
                        <label for="mergeaccount-member_id" class="col-sm-4 control-label">
                            Master Supporter
                        </label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                class="form-control
                                ds-members"
                                id="mastermemberid"
                                name="master_member_id"
                                placeholder="Search for a supporter&hellip;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        Merge Supporter
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

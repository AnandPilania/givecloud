<div role="tabpanel" class="tab-pane fade in" id="comments-app" data-account-id="{{ $member->id }}">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fas fa-edit fa-fw"></i> Notes
        </div>

        <div class="panel-body">
            <Comments
                commentable_path="{{ route('member.comments.index', ['member' => $member->getKey()]) }}"
                commentable_type="member"
                :commentable_id="{{ $member->id }}"
                :user_id="{{ user('id') }}"
                :is_account_admin="{{ user('is_account_admin') ? 'true' : 'false' }}"
            ></Comments>
        </div>
    </div>
</div>

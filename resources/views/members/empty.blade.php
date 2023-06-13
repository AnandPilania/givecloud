@extends('layouts.app')
@section('title', 'Supporters')

@section('content')
    <div class="feature-highlight">
        <img class="feature-img" src="/jpanel/assets/images/icons/account-stars.svg">
        <h2 class="feature-title">Track Your Supporters</h2>
        <p>This is where you'll track all your donors, customers and leads.</p>
        <div class="feature-actions">
            <a href="<?= e(route('backend.member.add')); ?>" class="btn btn-lg btn-success btn-pill"><i class="fa fa-plus"></i> Add a Supporter</a>
            <a href="https://help.givecloud.com/en/collections/931202-supporter-member-management" target="_blank" class="btn btn-lg btn-outline btn-primary btn-pill" rel="noreferrer"><i class="fa fa-book"></i> Learn More</a>
        </div>
    </div>
@endsection

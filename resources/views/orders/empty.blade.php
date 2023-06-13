@extends('layouts.app')
@section('title', 'Contributions')

@section('content')
    <div class="feature-highlight">
        <img class="feature-img" src="/jpanel/assets/images/icons/credit-card-cash.svg">
        <h2 class="feature-title">Track Your Donations &amp; Sales</h2>
        <p>This is where all your donations and sales will land for your review.</p>
        <div class="feature-actions">
            <a href="/jpanel/pos" target="_blank" class="btn btn-lg btn-success btn-pill"><i class="fa fa-plus"></i> Manually Add a Payment</a>
            <a href="https://help.givecloud.com/en/collections/931126-receiving-donations-contributions" target="_blank" class="btn btn-lg btn-outline btn-primary btn-pill" rel="noreferrer"><i class="fa fa-book"></i> Learn More</a>
        </div>
    </div>
@endsection

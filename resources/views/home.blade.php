@extends('layouts.app')

@section('content')
<div class="container-fluid hero-section ">
    <div class="col-7 text-center m-auto text-white p-5 ">
        <form class="searchbtn btn-dark-green">Search here</form>
        </div>
        <div class="grid-container">
    <div class="box box1 text-center  "><img src="img/Luzon.jpg" alt="Luzon"></div>
    <div class="Luzon">LUZON</div>
    <div class="box box2"><img src="img/Visayas.jpg" alt="Visayas"></div>
    <div class="Visayas">VISAYAS</div>
    <div class="box box3"><img src="img/Mindanao.jpg" alt="Mindanao"></div>
    <div class="Mindanao">MINDANAO</div>
</div>    


</div>

</div>
</div>
<x-product-list :products="$products" :admin="$admin"></x-product-list>
@endsection

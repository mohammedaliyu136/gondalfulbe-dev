@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Edit Inventory Item</h3>
    <form action="{{ route('inventories.update', $inventory->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="form-group mb-2">
            <label>Item Name</label>
            <input type="text" name="item_name" value="{{ $inventory->item_name }}" class="form-control" required>
        </div>
        <div class="form-group mb-2">
            <label>Description</label>
            <textarea name="description" class="form-control">{{ $inventory->description }}</textarea>
        </div>
        <div class="form-group mb-2">
            <label>Quantity</label>
            <input type="number" name="quantity" value="{{ $inventory->quantity }}" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection

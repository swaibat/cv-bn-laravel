<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreFiles extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['path', 'type', 'size', 'category_id', 'collection_id', 'user_id', 'product_id', 'gallery_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

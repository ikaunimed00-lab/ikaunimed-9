<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'product_category_id',
        'course_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'stock',
        'type',
        'membership_role',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function scopeDigitalWithoutCourse(Builder $query): Builder
    {
        return $query->where('type', 'digital')->whereNull('course_id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $product) {
            if ($product->type === 'digital' && ! $product->course_id) {
                throw ValidationException::withMessages([
                    'course_id' => 'Produk digital wajib dihubungkan ke course LMS.',
                ]);
            }

            if ($product->type !== 'digital' && $product->course_id) {
                throw ValidationException::withMessages([
                    'course_id' => 'Hanya produk digital yang boleh dihubungkan ke course.',
                ]);
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    protected function imageFiles(): Attribute
    {
        return Attribute::get(function () {
            return $this->images()
                ->orderBy('sort_order')
                ->pluck('path')
                ->toArray();
        });
    }
}

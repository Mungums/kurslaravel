<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'due_date',
        'description',
        'photo_path',
    ];

    /**
     * Автоматическое преобразование типов.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
    ];

    /**
     * Получить пользователя, который создал заказ.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

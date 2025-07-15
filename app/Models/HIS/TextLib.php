<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RtfHtmlPhp\Document;
use RtfHtmlPhp\Html\HtmlFormatter;

class TextLib extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'his_text_lib';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    protected $appends = [
        'text', // plainText của chuỗi TRF
    ]; 

    // Accessor để tự động base64_encode content khi get
    public function getContentAttribute($value)
    {
        return base64_encode($value);
    }
    public function getTextAttribute()
    {
        if (!$this->content) {
            return null;
        }
        return $this->getPlainText(base64_decode($this->content));
    }
    protected function getPlainText($rtf) 
    {
        try {
            $document = new Document($rtf); 
            $formatter = new HtmlFormatter('UTF-8');
            $html = $formatter->Format($document);

            // Lấy plain text bằng cách loại bỏ thẻ HTML
            $plainText = strip_tags($html);

            return trim($plainText);
        } catch (\Throwable $e) {
            // Log lỗi nếu cần
            return null;
        }
    }
}

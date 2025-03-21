<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Jobs\Util;

use App\Utils\Ninja;
use Illuminate\Http\File;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UploadAvatar implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $file;

    protected $directory;

    public function __construct($file, $directory)
    {
        $this->file = $file;
        $this->directory = $directory;
    }

    public function handle(): ?string
    {
        $tmp_file = sha1(time()).'.png'; //@phpstan-ignore-line

        $disk = Ninja::isHosted() ? 'backup' : config('filesystems.default');

        $im = imagecreatefromstring(file_get_contents($this->file));
        imagealphablending($im, false);
        imagesavealpha($im, true);
        $file_png = imagepng($im, sys_get_temp_dir().'/'.$tmp_file);

        $path = Storage::disk($disk)->putFile($this->directory, new File(sys_get_temp_dir().'/'.$tmp_file));

        $url = Storage::disk($disk)->url($path);

        //return file path
        if ($url) {
            return $url;
        } else {
            return null;
        }
    }
}

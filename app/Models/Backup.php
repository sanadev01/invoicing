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

namespace App\Models;

use App\Utils\Ninja;
use Illuminate\Support\Facades\Storage;

/**
 * App\Models\Backup
 *
 * @property int $id
 * @property int $activity_id
 * @property string|null $json_backup
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string $amount
 * @property string|null $filename
 * @property string|null $disk
 * @property-read \App\Models\Activity $activity
 * @property-read mixed $hashed_id
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel company()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|Backup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Backup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Backup query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel scope()
 * @method static \Illuminate\Database\Eloquent\Builder|Backup whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Backup whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Backup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Backup whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Backup whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Backup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Backup whereJsonBackup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Backup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Backup extends BaseModel
{
    public function getEntityType()
    {
        return self::class;
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
    
    /**
     * storeRemotely
     *
     * @param  string $html
     * @param  Client | Vendor $client_or_vendor
     * 
     * @return void
     */
    public function storeRemotely(?string $html, Client | Vendor $client_or_vendor)
    {
        if (empty($html)) {
            return;
        }

        $path = $client_or_vendor->backup_path().'/';
        $filename = now()->format('Y_m_d').'_'.md5(time()).'.html'; //@phpstan-ignore-line
        $file_path = $path.$filename;

        $disk = Ninja::isHosted() ? 'backup' : config('filesystems.default');

        Storage::disk($disk)->put($file_path, $html);

        $this->filename = $file_path;
        $this->disk = $disk;
        $this->save();
    }
    
    public function storeBackupFile($file)
    {
            
        $disk = Ninja::isHosted() ? 'backup' : config('filesystems.default');

        Storage::disk($disk)->put($this->filename, $file);

        $this->disk = $disk;
        $this->save();

    }
    /**
     * getFile
     *
     * pulls backup file from storage
     * 
     * @return mixed
     */
    public function getFile()
    {
        if(!$this->filename)
            return null;

        $disk = Ninja::isHosted() ? $this->disk : config('filesystems.default');

        return Storage::disk($disk)->get($this->filename);

    }
    
    /**
     * deleteFile
     *
     * removes backup file from storage
     * 
     * @return void
     */
    public function deleteFile()
    {
        nlog('deleting => '.$this->filename);

        if (!$this->filename) {
            return;
        }

        $disk = Ninja::isHosted() ? $this->disk : config('filesystems.default');

        try {
            Storage::disk($disk)->delete($this->filename);
        } catch (\Exception $e) {
            nlog('BACKUPEXCEPTION deleting backup file with error '.$e->getMessage());
        }
    }
}

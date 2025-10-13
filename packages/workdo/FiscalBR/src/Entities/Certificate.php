<?php

namespace Workdo\FiscalBR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;

class Certificate extends Model
{
    use HasFactory;

    protected $table = 'fiscalbr_certificates';

    protected $fillable = [
        'workspace_id',
        'nome',
        'certificado',
        'senha',
        'validade',
        'ativo',
    ];

    protected $casts = [
        'validade' => 'date',
        'ativo' => 'boolean',
    ];

    protected $hidden = [
        'certificado',
        'senha',
    ];

    /**
     * Get the workspace that owns the certificate.
     */
    public function workspace()
    {
        return $this->belongsTo(\App\Models\WorkSpace::class, 'workspace_id');
    }

    /**
     * Set encrypted certificate.
     */
    public function setCertificadoAttribute($value)
    {
        $this->attributes['certificado'] = Crypt::encryptString($value);
    }

    /**
     * Get decrypted certificate.
     */
    public function getCertificadoAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    /**
     * Set encrypted password.
     */
    public function setSenhaAttribute($value)
    {
        $this->attributes['senha'] = Crypt::encryptString($value);
    }

    /**
     * Get decrypted password.
     */
    public function getSenhaAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    /**
     * Check if certificate is valid.
     */
    public function isValid(): bool
    {
        return $this->ativo && $this->validade->isFuture();
    }
}


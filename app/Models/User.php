<?php

namespace App\Models;

use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\UserActivated;
use App\Notifications\TempStructureVerified;
use App\Notifications\CustomResetPassword;

class User extends Authenticatable implements MustVerifyEmail
{

  protected static function booted()
  {
      static::updating(function ($user) {
          if ($user->isDirty('is_verified') && $user->is_verified) {
              $user->notify(new UserActivated());

              if (
                  $user->role === 'structure' &&
                  $user->temporary_structure_id
              ) {
                  $tempStructure = $user->temporaryStructure;

                  if ($tempStructure && $tempStructure->added_by) {
                      $creator = User::find($tempStructure->added_by);
                      if ($creator) {
                          $creator->notify(new TempStructureVerified());
                      }
                  }
              }
          }
      });
  }

  public function sendPasswordResetNotification($token)
{
    $this->notify(new CustomResetPassword($token));
}


    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'email',
        'password',
        'role',
        'sector',
        'profession',
        'country_id',
        'website',
        'bio',
        'is_verified',
        'verified_at',
        'verified_by',
        'temporary_structure_id',
        'profile_picture',
        'social_medias',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'social_medias' => 'array',
        'sector' => 'string',
        'profession' => 'string',
    ];

    protected $appends = ['profile_picture_url'];


    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function temporaryStructure()
    {
        return $this->belongsTo(TemporaryStructure::class, 'temporary_structure_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function approvedProjects()
    {
        return $this->hasMany(Project::class, 'approved_by');
    }

    public function addedTemporaryStructures()
    {
        return $this->hasMany(TemporaryStructure::class, 'added_by');
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    public function awards()
 {
    return $this->hasMany(Award::class);
 }

    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture
            ? asset('storage/' . $this->profile_picture)
            : null;
    }

    public function sendEmailVerificationNotification()
{
    $this->notify(new VerifyEmail());
}

}

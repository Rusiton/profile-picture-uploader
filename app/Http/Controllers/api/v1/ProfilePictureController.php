<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\UploadProfilePictureRequest;
use App\Http\Resources\api\v1\ProfilePictureResource;
use App\Models\ProfilePicture;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProfilePictureController extends Controller implements HasMiddleware
{
    protected $cloudinary;

    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('verified'),
        ];
    }

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key' => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
        ]);
    }

    public function index() {
        $pictures = ProfilePicture::whereNotNull('profile_picture')->with('user')->paginate(30);

        $pictures = [
            ...collect($pictures),
            'data' => $pictures->map(function ($picture) {
                return new ProfilePictureResource($picture);
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'pages' => $pictures,
            ],
    ]);
    }

    public function upload(UploadProfilePictureRequest $request) {
        try {
            $file = $request->file('profilePicture');
            $user = $request->user();

            $uploadResult = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => 'profile-pictures',
                    'public_id' => 'user_' . $user->token,
                    'overwrite' => true,
                    'transformation' => [
                        [
                            'width' => 400,
                            'height' => 400,
                            'crop' => 'fill',
                            'gravity' => 'face',
                        ],
                        [
                            'quality' => 'auto',
                        ],
                        [
                            'fetch_format' => 'auto',
                        ],
                    ],
                ],
            );

            $user->picture->update([
                'profile_picture' => $uploadResult['secure_url'],
                'profile_picture_public_id' => $uploadResult['public_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture was uploaded successfully',
                'data' => [
                    'url' => $uploadResult['secure_url'],
                    'public_id' => $uploadResult['public_id'],
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request) {
        try {
            $user = $request->user();

            if (!$user->picture->profile_picture_public_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No profile picture to delete',
                ], 404);
            }

            $this->cloudinary->uploadApi()->destroy($user->picture->profile_picture_public_id);

            $user->picture->update([
                'profile_picture' => null,
                'profile_picture_public_id' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deletion failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getUserProfilePicture() {
        $user = request()->user();

        return response()->json([
            'success' => true,
            'data' => [
                'url' => $user->picture->profile_picture,
                'public_id' => $user->picture->profile_picture_public_id,
            ],
        ], 200);
    }
}

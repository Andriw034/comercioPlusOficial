'name'               => $data['name'],
            'email'              => $data['email'],
            'password'           => Hash::make($data['password']),
            'profile_photo_path' => $profilePhotoPath,
        ]);
=======
        $user = User::create([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'password'           => Hash::make($data['password']),
            'avatar_path'        => $profilePhotoPath,
        ]);

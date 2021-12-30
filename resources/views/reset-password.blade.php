<p>Hello {{ $username }},</p>
<p>This is a password reset email.</p>
<p>Reset your password using the following link. Your password reset token will be valid for only 3 hours.</p>
<p>{{ $base }}#/reset-password?email={{ $email }}&token={{ $rid }}</p>
Regards,<br>
Quiz n Stuff Admin.
</p>
<?php

class Model_User extends Model_Auth_User{
	
	/**
	 * Labels for fields in this model
	 *
	 * @return array Labels
	 */
	public function labels()
	{
		return array(
			'username'         => 'Your username',
			'email'            => 'Your email address',
			'password'         => 'Your password',
		);
	}
	
	public function not_verified(){
		$qry_login = DB::select('*')->from('roles_users')
					->where('roles_users.user_id', '=', DB::expr('user.id'))
					->where('roles_users.role_id', '=', '1');
					
		return $this->where('verified', '=', '1')
                            ->where('rejected', '=', '0')
					->where(NULL, 'not exists', $qry_login);
	}
	
	/**
	 * Password validation for plain passwords.
	 *
	 * @param array $values
	 * @return Validation
	 */
	public static function get_password_validation($values)
	{
		return parent::get_password_validation($values)
			->label('password_confirm', 'Your confirm password');
	}
}

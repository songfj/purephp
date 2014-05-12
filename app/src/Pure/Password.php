<?php

/**
 * Facade class for password_* functions
 */
class Pure_Password {

    /**
     * Returns information about the given hash
     * @link http://php.net/manual/en/function.password-get-info.php
     * @param string $hash <p>
     * A hash created by <b>password_hash</b>.
     * </p>
     * @return array an associative array with three elements:
     * algo, which will match a
     * password algorithm constant
     * algoName, which has the human readable name of the
     * algorithm
     * options, which includes the options
     * provided when calling <b>password_hash</b>
     */
    public static function info($hash) {
        return password_get_info($hash);
    }

    /**
     * Creates a password hash
     * @link http://php.net/manual/en/function.password-hash.php
     * @param string $password <p>
     * The user&#x00027;s password.
     * </p>
     * <p>
     * Using the <b>PASSWORD_BCRYPT</b> for the
     * <i>algo</i> parameter, will result
     * in the <i>password</i> parameter being truncated to a
     * maximum length of 72 characters. This is only a concern if are using
     * the same salt to hash strings with this algorithm that are over 72
     * bytes in length, as this will result in those hashes being identical.
     * </p>
     * @param integer $algo <p>
     * A password algorithm constant denoting the algorithm to use when hashing the password.
     * </p>
     * @param array $options [optional] <p>
     * An associative array containing options. See the password algorithm constants for documentation on the supported options for each algorithm.
     * </p>
     * <p>
     * If omitted, a random salt will be created and the default cost will be
     * used.
     * </p>
     * @return string the hashed password, or <b>FALSE</b> on failure.
     * </p>
     * <p>
     * The used algorithm, cost and salt are returned as part of the hash. Therefore,
     * all information that's needed to verify the hash is included in it. This allows
     * the <b>password_verify</b> function to verify the hash without
     * needing separate storage for the salt or algorithm information.
     */
    public static function hash($password, $algo, array $options = null) {
        return password_hash($password, $algo, $options);
    }

    /**
     * Verifies that a password matches a hash
     * @link http://php.net/manual/en/function.password-verify.php
     * @param string $password <p>
     * The user&#x00027;s password.
     * </p>
     * @param string $hash <p>
     * A hash created by <b>password_hash</b>.
     * </p>
     * @return boolean <b>TRUE</b> if the password and hash match, or <b>FALSE</b> otherwise.
     */
    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Checks if the given hash matches the given options
     * @link http://php.net/manual/en/function.password-needs-rehash.php
     * @param string $hash <p>
     * A hash created by <b>password_hash</b>.
     * </p>
     * @param string $algo <p>
     * A password algorithm constant denoting the algorithm to use when hashing the password.
     * </p>
     * @param string $options [optional] <p>
     * An associative array containing options. See the password algorithm constants for documentation on the supported options for each algorithm.
     * </p>
     * @return boolean <b>TRUE</b> if the hash should be rehashed to match the given
     * <i>algo</i> and <i>options</i>, or <b>FALSE</b>
     * otherwise.
     */
    public static function needsRehash($password, $algo, array $options = null) {
        return password_needs_rehash($password, $algo, $options);
    }

}

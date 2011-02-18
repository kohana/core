# Validation

*This page needs to be reviewed for accuracy by the development team. Better examples would be helpful.*

Validation can be performed on any array using the [Validate] class. Labels, filters, rules, and callbacks can be attached to a Validate object by the array key, called a "field name".

labels
:  A label is a human-readable version of the field name.

filters
:  A filter modifies the value of an field before rules and callbacks are run.

rules
:  A rule is a check on a field that returns `TRUE` or `FALSE`. If a rule
   returns `FALSE`, an error will be added to the field.

callbacks
:  A callback is custom method that can access the entire Validate object.
   The return value of a callback is ignored. Instead, the callback must
   manually add an error to the object using [Validate::error] on failure.

[!!] Note that [Validate] callbacks and [PHP callbacks](http://php.net/manual/language.pseudo-types.php#language.types.callback) are not the same.

Using `TRUE` as the field name when adding a filter, rule, or callback will by applied to all named fields.

**The [Validate] object will remove all fields from the array that have not been specifically named by a label, filter, rule, or callback. This prevents access to fields that have not been validated as a security precaution.**

Creating a validation object is done using the [Validate::factory] method:

    $post = Validate::factory($_POST);

[!!] The `$post` object will be used for the rest of this tutorial. This tutorial will show you how to validate the registration of a new user.

### Default Rules

Validation also comes with several default rules:

Rule name                 | Function
------------------------- |-------------------------------------------------
[Validate::not_empty]     | Value must be a non-empty value
[Validate::regex]         | Match the value against a regular expression
[Validate::min_length]    | Minimum number of characters for value
[Validate::max_length]    | Maximum number of characters for value
[Validate::exact_length]  | Value must be an exact number of characters
[Validate::email]         | An email address is required
[Validate::email_domain]  | Check that the domain of the email exists
[Validate::url]           | Value must be a URL
[Validate::ip]            | Value must be an IP address
[Validate::phone]         | Value must be a phone number
[Validate::credit_card]   | Require a credit card number
[Validate::date]          | Value must be a date (and time)
[Validate::alpha]         | Only alpha characters allowed
[Validate::alpha_dash]    | Only alpha and hyphens allowed
[Validate::alpha_numeric] | Only alpha and numbers allowed
[Validate::digit]         | Value must be an integer digit
[Validate::decimal]       | Value must be a decimal or float value
[Validate::numeric]       | Only numeric characters allowed
[Validate::range]         | Value must be within a range
[Validate::color]         | Value must be a valid HEX color
[Validate::matches]       | Value matches another field value

[!!] Any method that exists within the [Validate] class may be used as a validation rule without specifying a complete callback. For example, adding `'not_empty'` is the same as `array('Validate', 'not_empty')`.

## Adding Filters

All validation filters are defined as a field name, a method or function (using the [PHP callback](http://php.net/manual/language.pseudo-types.php#language.types.callback) syntax), and an array of parameters:

    $object->filter($field, $callback, $parameter);

Filters modify the field value before it is checked using rules or callbacks.

If we wanted to convert the "username" field to lowercase:

    $post->filter('username', 'strtolower');

If we wanted to remove all leading and trailing whitespace from *all* fields:

    $post->filter(TRUE, 'trim');

## Adding Rules

All validation rules are defined as a field name, a method or function (using the [PHP callback](http://php.net/callback) syntax), and an array of parameters:

    $object->rule($field, $callback, $parameter);

To start our example, we will perform validation on a `$_POST` array that contains user registration information:

    $post = Validate::factory($_POST);

Next we need to process the POST'ed information using [Validate]. To start, we need to add some rules:

    $post
        ->rule('username', 'not_empty')
        ->rule('username', 'regex', array('/^[a-z_.]++$/iD'))

        ->rule('password', 'not_empty')
        ->rule('password', 'min_length', array('6'))
        ->rule('confirm',  'matches', array('password'))

        ->rule('use_ssl', 'not_empty');

Any existing PHP function can also be used a rule. For instance, if we want to check if the user entered a proper value for the SSL question:

    $post->rule('use_ssl', 'in_array', array(array('yes', 'no')));

Note that all array parameters must still be wrapped in an array! Without the wrapping array, `in_array` would be called as `in_array($value, 'yes', 'no')`, which would result in a PHP error.

Any custom rules can be added using a [PHP callback](http://php.net/manual/language.pseudo-types.php#language.types.callback]:

    $post->rule('username', 'User_Model::unique_username');

[!!] Currently (v3.0.7) it is not possible to use an object for a rule, only static methods and functions.

The method `User_Model::unique_username()` would be defined similar to:

    public static function unique_username($username)
    {
        // Check if the username already exists in the database
        return ! DB::select(array(DB::expr('COUNT(username)'), 'total'))
            ->from('users')
            ->where('username', '=', $username)
            ->execute()
            ->get('total');
    }

[!!] Custom rules allow many additional checks to be reused for multiple purposes. These methods will almost always exist in a model, but may be defined in any class.

## Adding callbacks

All validation callbacks are defined as a field name and a method or function (using the [PHP callback](http://php.net/manual/language.pseudo-types.php#language.types.callback) syntax):

    $object->callback($field, $callback);

The user password must be hashed if it validates, so we will hash it using a callback:

    $post->callback('password', array($model, 'hash_password'));

This would assume that the `$model->hash_password()` method would be defined similar to:

    public function hash_password(Validate $array, $field)
    {
        if ($array[$field])
        {
            // Hash the password if it exists
            $array[$field] = sha1($array[$field]);
        }
    }

# A Complete Example

First, we need a [View] that contains the HTML form, which will be placed in `application/views/user/register.php`:

    <?php echo Form::open() ?>
    <?php if ($errors): ?>
    <p class="message">Some errors were encountered, please check the details you entered.</p>
    <ul class="errors">
    <?php foreach ($errors as $message): ?>
        <li><?php echo $message ?></li>
    <?php endforeach ?>
    <?php endif ?>

    <dl>
        <dt><?php echo Form::label('username', 'Username') ?></dt>
        <dd><?php echo Form::input('username', $post['username']) ?></dd>

        <dt><?php echo Form::label('password', 'Password') ?></dt>
        <dd><?php echo Form::password('password') ?></dd>
        <dd class="help">Passwords must be at least 6 characters long.</dd>
        <dt><?php echo Form::label('confirm', 'Confirm Password') ?></dt>
        <dd><?php echo Form::password('confirm') ?></dd>

        <dt><?php echo Form::label('use_ssl', 'Use extra security?') ?></dt>
        <dd><?php echo Form::select('use_ssl', array('yes' => 'Always', 'no' => 'Only when necessary'), $post['use_ssl']) ?></dd>
        <dd class="help">For security, SSL is always used when making payments.</dd>
    </dl>

    <?php echo Form::submit(NULL, 'Sign Up') ?>
    <?php echo Form::close() ?>

[!!] This example uses the [Form] helper extensively. Using [Form] instead of writing HTML ensures that all of the form inputs will properly handle input that includes HTML characters. If you prefer to write the HTML yourself, be sure to use [HTML::chars] to escape user input.

Next, we need a controller and action to process the registration, which will be placed in `application/classes/controller/user.php`:

    class Controller_User extends Controller {

        public function action_register()
        {
            $user = Model::factory('user');

            $post = Validate::factory($_POST)
                ->filter(TRUE, 'trim')

                ->filter('username', 'strtolower')

                ->rule('username', 'not_empty')
                ->rule('username', 'regex', array('/^[a-z_.]++$/iD'))
                ->rule('username', array($user, 'unique_username'))

                ->rule('password', 'not_empty')
                ->rule('password', 'min_length', array('6'))
                ->rule('confirm',  'matches', array('password'))

                ->rule('use_ssl', 'not_empty')
                ->rule('use_ssl', 'in_array', array(array('yes', 'no')))

                ->callback('password', array($user, 'hash_password'));

            if ($post->check())
            {
                // Data has been validated, register the user
                $user->register($post);

                // Always redirect after a successful POST to prevent refresh warnings
                $this->request->redirect('user/profile');
            }

            // Validation failed, collect the errors
            $errors = $post->errors('user');

            // Display the registration form
            $this->request->response = View::factory('user/register')
                ->bind('post', $post)
                ->bind('errors', $errors);
        }

    }

We will also need a user model, which will be placed in `application/classes/model/user.php`:

    class Model_User extends Model {

        public function register($array)
        {
            // Create a new user record in the database
            $id = DB::insert(array_keys($array))
                ->values($array)
                ->execute();

            // Save the new user id to a cookie
            cookie::set('user', $id);

            return $id;
        }

    }

That is it, we have a complete user registration example that properly checks user input!
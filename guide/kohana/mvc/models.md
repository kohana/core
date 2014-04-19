# Models

A [Model] notifies its associated [views](mvc/views) and [controllers](mvc/controllers) when there has been a change in its state. 
This notification allows the views to produce updated output, and the controllers to change the available set of commands. 
In some cases an MVC implementation might instead be *passive*, so that other components must poll the model for updates rather than being notified.

Creating a simple model:

	class Model_Post extends Model {

		public function do_stuff()
		{
			// This is where you do domain logic...
		}

	}

If you want database access, have your model extend the [Model_Database] class:

	class Model_Post extends Model_Database {

		public function do_stuff()
		{
			// This is where you do domain logic...
		}

		public function get_stuff()
		{
			// Get stuff from the database
			return $this->db->query('...');
		}

	}

If you want CRUD/ORM capabilities, see the [ORM](../../guide/orm) module.

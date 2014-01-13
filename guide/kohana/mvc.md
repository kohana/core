# Model–View–Controller

Model–View–Controller (MVC) is a pattern for implementing user interfaces. 
It divides a given application into three interconnected parts, so as to separate internal representations of information from the ways that information is presented to or accepted from the user. 
The central component, the model, consists of application data, business rules, logic, and functions. 
A view can be any output representation of information, such as a chart or a diagram. 
Multiple views of the same information are possible, such as a bar chart for management and a tabular view for accountants. The third part, the controller, accepts input and converts it to commands for the model or view.

## Component interactions

In addition to dividing the application into three kinds of components, the MVC design defines the interactions between them.

* A [controller](mvc/controllers) can send commands to the model to update the model's state. It can also send commands to its associated view to change the view's presentation of the model.
* A [model](mvc/models) notifies its associated views and controllers when there has been a change in its state. This notification allows the views to produce updated output, and the controllers to change the available set of commands. In some cases an MVC implementation might instead be *passive*, so that other components must poll the model for updates rather than being notified.
* A [view](mvc/views) requests information from the model that it needs for generating an output representation to the user

![MVC-Process](http://upload.wikimedia.org/wikipedia/commons/a/a0/MVC-Process.svg)

For more info, see [wikibooks article](http://wikibooks.org/wiki/Computer_Science_Design_Patterns/Model–view–controller).

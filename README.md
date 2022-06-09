# CiviCRM Resource Management

## Introduction

Management of resources in CiviCRM often requires a somewhat custom approach.
CiviVolunteer has some cool features but also a specified set of workflows which
can be hard to adapt or to extend. Using physical resources was always
complicated - we have seen a lot of contact subtypes called "Projector", "Room",
etc.

Particularly volunteer organisations need to be able to track their supporters's
qualifications in detail as well as to manage opportunities for volunteers to
engage in. Engagement opportunities can have a number of resource demands (both
for human and for physical resources). For example if you organise an event you
need a specified number of helpers, some medical staff, a vehicle, projectors
and much more.

Depending on the use case and size of the organization, defining the setup and
matching the demands to resources can become rather complex which is why we
decided to create a framework to define and manage resources and their
availabilities, resource demands and mechanisms to match those.

This extension can be used individually but for many use cases you may want to
integrate it with other extensions described below.

The extension is licensed under
[AGPL-3.0](https://github.com/systopia/de.systopia.resource/blob/master/LICENSE.txt)
.

## Scope & Features

### [Resource Management extension](https://github.com/systopia/de.systopia.resource)

The main extension provides an UI to define resources and demands and an
algorithm to match those. When a resource is assigned to a demand it will be
blocked for the specified timeframe. You can also manage resource's general
availability restrictions (such as holidays).

For each resource demand, conditions can be specified, particularly in which
time frame the resource is required but also other conditions such as groups the
resource needs to be a part of and much more.

The extension will suggest resources matching your demands and allow you to
assign them. Also some online integration features are already implemented (
inviting contacts to participate in a matching opportunity) and much more is
planned for the future

### [Entity Construction Kit](https://github.com/systopia/de.systopia.eck)

This extension enables you to create custom entities via CiviCRM's UI and assign
custom fields to your entities. In the context of resource management you will
only need it if you want to manage physical / non-CiviCRM-contact resources.

In that case you would create a custom entity type and/or sub types for each
category of resource you want to manage.

### Search Kit

CiviCRM's search kit (core) extension allows you to build versatile searches,
list views and much more. As the entity construction kit only provides limited
UI by itself you will have to use the search kit if you want to find, create and
edit your custom entities.

If you only manage contact resources you do not neccesarily need the search kit
but it can help you to build much better searches and views.

### [Resource Event](https://github.com/systopia/de.systopia.resourceevent)

In short, this extension creates a special participant object in CiviCRM
whenever a resource is assigned to a demand. It will also change the participant
object's status when a resource assigment or connected participant object is
edited.

It is not required but it will most likely be useful in many cases. For example,
by having a participant object for each assigment you can use many core
features (such as searches and reports to filter assigments) for which you would
otherwise have to use custom features.

Moreover it allows you to dig into the ever growing set of extensions for events
such as [Event Messages](https://github.com/systopia/de.systopia.eventmessages),
[Event Invitation](https://github.com/systopia/de.systopia.eventinvitation),
[Event Checkin] (https://github.com/systopia/de.systopia.eventcheckin) or
[Remote Events](https://github.com/systopia/de.systopia.remoteevent). This is
particularly useful if you plan to provide online integration features for your
volunteers.

### [Event Invitation](https://github.com/systopia/de.systopia.eventinvitation)

This extension (and it's dependencies) allows you to invite contacts in CiviCRM
to an event and provides a simple feedback form where contacts can choose
whether they can attend or not. If you use the Resource Event extension its
scope will extend and allow you to invite contacts / volunteers to "participate
as a resource" for a demand you have specified in CiviCRM. You do not need this
extension unless you want to use this feature.

### Remarks & Limitations

The extensions' architecture was designed with rather complex use cases in mind
but we tried to make it as accessible as possible for less complicated
requirements. Currently, UI features can only be used in combination with
CiviEvent. It is well possible that in the future all or some features will also
be made available with a standalone UI.

The extension has no permission system yet.

We are currently planning a second project phase which will likely have a focus
on online integration using features of
[Remote Events](https://github.com/systopia/de.systopia.remoteevent) and
[Remote Tools](https://github.com/systopia/de.systopia.remotetools) and
integrate with Drupal9 using [CiviMRF](https://github.com/CiviMRF/cmrf_core),
[CiviRemote & CiviRemote Events](https://github.com/systopia/civiremote). Let us
know if you are interested to participate!

## Configuration / Usage

Here we refer to the Resource Management as well as the Resource Event
extension. For configuration / usage of the other extensions mentioned above,
please refer to the respective documentation. After installing the Resource
Management extension no further configuration is required. In case you use the
Resource Event extension see below for configuration and further information.

### Make contacts available as resources

First you will need to declare some contacts to be available as (human)
resources. You can do this for single contact by navigating to the tab "Resource
Assignment", clicking on "Create Resource" and defining a resource label and
type. You can also do that with the bulk action "Mark as Resource" from any
search result.

### Define & manage resource demands

When configuring any CiviCRM Event you will find a new tab titled "Resources".
Currently this is the only place where you can define resource demands. When
adding a demand choose the type and label of the demand along with the number of
required resources.

After adding a demand it is usually advisable to create a condition by clicking
on "modify" in the conditions column. Availability conditions will define the
timespan the resource(s) are required which can be absolute or relative in
regard to the event's start and end date. __Be careful - if you do not specify
an availability condition the assigned resource will not be matched to any other
demands indefinitely!__

You can also add other conditions that need to be met in order to assign / match
resources, e.g. that the contacts need to be in a specific group or have a
certain tag.

### Resource availability restrictions

Resources that are assigned to a demand won't be matched to / suggested for
conflicting demands. In addition, for any resource, general availability
restrictions (e.g. holidays / other absence) can be specified within the
Resource Assignments tab. These general availability restrictions will aso
prevent the resource to be suggested for other assignments during that timespan(
s).

### Matching & mananging resources for a demand

In order to find resources that fulfill the specified conditions click on "
assign" in the actions column of the demand configuration screen. You will be
presented with a list of matching resources (if any). You can choose selected or
all resources from that list and assign them to the demand.

The columns "assigned" and "fulfilled" will show the number of all resources
that are assigned and those that fulfill all currently defined conditions.
Clicking on a number in the assigned-column will bring up a list of the
currently assigned resources and allow you to unassign them if required.

### Find matching demands for resources

From a resource's assignment tab, demands with conditions met by the resources
can be found by clicking on the "+" in the Assignments-area". Assign a resource
to a demand from the list of demands the resource would be suitable for. You can
also unassign a resource from the list of current assignments.

### [Event Resource Extension](https://github.com/systopia/de.systopia.resourceevent)

As described above, this extension builds upon the Resource Management extension
for assigning resources to CiviCRM participants. After installing the extension,
navigate to its settings page (/civicrm/admin/setting/resourceevent?reset=1) and
define the positive and negative status that you want to be used by the
extension when creating / updating your participant objects. The extension will
create a special participant role "Human Resource" which cannot be added when
creating or editing a participant object via the UI. It will automatically
create and update participant objects for resource assigments and vice versa:

- When assigning a resource to a demand, an existing participant object with
  Human Resource role would be updated, otherwise a new one would be created.
- When unassigning a resource from a demand the connected participant object
  will be updated to your configured participant status with a negative class.
- When creating a participant object with a positive status that has the role
  Human resource, and a connected demand a corresponding assignment will be
  created. Note that this is currently not possible via the UI but only via
  extensions (e.g. Event Invitation) or the API.
- When deleting or changing a participant object with the role Human Resource to
  a negative status, a connected assignment will be deleted as well.

Please note that a more complex "status synchronisation" model may be
implemented in the future.

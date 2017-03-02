In the webform content type create a field
"webform_results_access" of type "User Reference" (multiple values).

On "Manage Display" hide the field (unless you want everybody to know who may
access the results).

depends on References module : "https://www.drupal.org/project/references" (for user reference field)


## Instructions:

##### 0. Preparation:
- Clear Drupal cache (just to be on the safe side)
- Download install and activate this module : https://www.drupal.org/project/references
- Download install this module from github (don't activate it yet): https://github.com/plirof2/d7_webform_results_access 

##### 1. Add a new field to the webform:
Go to webform manage fields (eg http://mydrupalsite/subpath/admin/structure/types/manage/webform/fields ) and 
- add a **new field** to the webform named : "**webform_results_access**" .
- the field type should be "User reference" (you need the References module activated (https://www.drupal.org/project/references)    )
- The graphic component should be "**Autocomplete text field**"
- The machine name should be "field_webform_results_access" (this is the default value so no need to change it)

#### 2. Set some settings for this field:
- Go to http://mydrupalsite/subpath/admin/structure/types/manage/webform/fields/field_webform_results_access
- select "**number of values**"/"Αριθμός τιμών" to "**Unlimited**" (we want to be able to add unlimited users to view results)
- from the option "**User roles that can be referenced**" select a user group - usually "registered-authanticated users"/"πιστοποιημένος χρήστης"
- Maybe you want to restrict visibility and permission from the option "**Field visibility and permissions**".

#### 3. Activate module
- Clear Drupal cache (just to be on the safe side)
- Activate module d7_webform_results_access

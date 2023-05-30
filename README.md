# Virtual Programming Laboratory - ILIAS plugin

The virtual programming lab (ViPLab) is a browser-based programming environment.
This plugin integrates the question type _ViPLab_ in [ILIAS](https://www.ilias.de/) and allows for providing
programming tasks in context of test and assessment.  

The plugin is a part of the ViPLab infrastructure mostly hosted and provided by the University of Stuttgart.
In order to gain access to the required message middleware and further ViPLab backends, please contact
the [ILIAS support](mailto:ilias-support@tik.uni-stuttgart.de).
This page provides quick setup instructions and a compact overview of the plugin settings to be configured by
ILIAS administrators. 

---

## Components

- VipLab ILIAS plugin
    - Javascript (GWT) based frontend for teachers and students
    - ILIAS integration for test and assessment
    - Import and export with respect to the QTI specification
- VipLab Backend¹
    - Scalable program code compilation and evaluation
    - Supported languages: C, C++, DuMuX, Java, Matlab and Octave
- E-learning Community Server (ECS)¹
    - Message-based communication between ViPLab components within and across communities
    - Message queuing
- VipEval¹  (optional service)
    - Evaluation and scoring service for automatically evaluable programming tasks

¹ provided as a service by the University of Stuttgart

## Preparation and Install

### General requirements and dependencies
- **ILIAS** (min. version 5.4.0,  max. version 7.999)
- **VipLabCron**  (ILIAS plugin)
- **VipLabEvent**  (ILIAS plugin)

### Required access rights
- **Root**  access to the ILIAS installation on the webserver
- **Admin**  rights within the ILIAS system

### Webserver preparation
**Important**:  The ViPLab frontend sends requests to URLs (ECS server) that do not match with the origin. To avoid conflicts with the CORS policies, a ProxyPass must be specified on the web server which hosts ILIAS or on the load balancer (e.g., nginx) which routes requests to the ILIAS web server. The local path `/_ecs_`  must be mapped to `https://vip.uni-stuttgart.de` for request as well as for response headers.

The following example shows a ProxyPass specified within an Apache configuration.

```editorconfig
<VirtualHost *:443>
  ServerName ilias.example-university.edu
  DocumentRoot "/var/www/ilias"
 
  # ...
 
  # ProxyPass rules
  SSLProxyEngine On
  ProxyRequests Off
  ProxyPreserveHost Off
  ProxyPass /_ecs_ https://vip.uni-stuttgart.de
  ProxyPassReverse /_ecs_ https://vip.uni-stuttgart.de
</VirtualHost>
```

Note that the apache modules `ssl`, `proxy`  and `proxy_http` must also be enabled:
```bash
a2enmod ssl proxy proxy_http
systemctl restart apache.service
```

### Install
1. Access the installation directory of your running ILIAS instance (e.g.,  `/var/www/ilias`) and clone the VipLab plugin with its dependencies:  
    ```
    cd /var/www/ilias
    git clone https://github.com/TIK-NFL/ViPLab.git ./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assViPLab
    git clone https://github.com/TIK-NFL/ViPLabCron.git ./Customizing/global/plugins/Services/Cron/CronHook/ViPLabCron
    git clone https://github.com/TIK-NFL/ViPLabEvent.git ./Customizing/global/plugins/Services/EventHandling/EventHook/ViPLabEvent
    ```
2. Access ILIAS by a web browser and go to:  **Administration  →  Extending ILIAS  →  Plugins**.
3. Install the ViPLabCron, ViPLabEvent  and the assViPLab plugins respectively (**Actions → Install**).
4. Activate the ViPLabCron, ViPLabEvent  and the assViPLab plugins respectively (**Actions → Activate**).

## Configuration

### ECS
1. Access ILIAS by a web browser and and create a new category 'ViPLab' for course links. Note the category id which can be read from the URL parameter `ref_id`.
2. Go to  **Administration  →  Extending ILIAS  →  ECS →  Add New ECS**.
3. Configure as follows:

|                    Property | Value                                           |
|----------------------------:|-------------------------------------------------|
|    Enable ECS Functionality | [X]                                             |
|   Name of ECS Configuration | VIPLab ECS                                      |
|                  Server URL | `ilias.example-university.edu/_ecs_`            |
|                    Protocol | HTTPS                                           |
|                        Port | 443                                             |
|         Authentication Type | (o) Username/Password (provided by U Stuttgart) |
|                   Import ID | `ref_id`  of the course created before          |
|             Role Assignment | Guest                                           |
| Activation Period Extension | 6                                               |

### ViPLab Plugin
1. Access ILIAS and go to  **Administration  →  Extending ILIAS  →  Plugins**.
2. On the  assViPLab  entry, click:  **Actions  →  Configure**.
3. Select the  ECS server  to be used by VipLab and **save**.
4. Optionally, select the  _Membership ID_  (MID) of the server for automated corrections (Evaluation Backend) and receipt (Receiver for Scoring Results).
5. Map each supported language to the server (MID) the calculation tasks will be processed on.
   - Productive systems are more stable compared to test systems.
   - Test systems offer more features, such as newer compiler flags.
6. Save again.

### Integration (optional)
1. Activate the ViPLabCron in **Administration → System Settings and Maintenance → Cron Jobs**.
2. Activate manual scoring of ViPLab questions in **Administration → Repository and Objects → Test and Assessment**.

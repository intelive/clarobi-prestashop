# ClaroBi

## About
[ClaroBi][clarobi]

Allows ClaroBi to gather information from Products, Stocks, Orders, Invoices, Customers, Abandoned Carts.  
This module is available for PrestaShop versions: 1.6.x - 1.7.x


#### Product page on PrestaShop Addons:
[Addon-ClaroBi][addons]


## Module version guide

| PrestaShop version | Module version |  Repo                | Doc                |  PHP Version |
|--------------------|----------------|---------------------|---------------------|-------------|
| 1.6.x - 1.7        | 1.0.0          |  [release/1.0.0][clarobi-repo] | |   7.3.x or greater    |

## Requirements

1. PHP version  7.3.x
2. cURL enabled


## Installation

To install module on PrestaShop, you an get it from PrestaShop market place or from out *GitHub* repository.


### Install from PrestaShop Addons

Download zip package form [product page on PrestaShop Addons][addons].

Once the download is complete follow the steps:
* Connect to the *BackOffice* of your shop  
* Go to *Back Office >> Modules >> Module Manager*
* Click *Upload a module* and drag and drop the zip folder of the module
* After the installation is complete locate the new module in the list under *Module >> Module Manager*  
    * Scroll down if necessary
    * Refresh the *BackOffice* if necessary
* Click *Configure* to finish the setup

### Install from GitHub

To install from repository you can either download the zip package or clone the repository.
You can find it at [ClaroBi module repo][clarobi-repo].

#### Download zip package
 Once you access our repo, click *Clone or Download* and choose *Download ZIP*.
 After the download is complete, rename module folder to `clarobi`.  
 Now, you can either:
 1. unzip the folder and place it under PrestaShop_folder_root >> modules, if you have access to project  
 or
 2. go to *BackOffice >> Modules >> Module Manager* and click *Upload a module* and select the path to the module zip folder
 
#### Clone repository

To clone the repo follow the steps:
* open *GitBash* console and go to path where you want to save the project
* run the command: `git clone git@github.com:intelive/clarobi-prestashop.git clarobi`
    * Note: the name of the folder in which the repository will be clone must be `clarobi`

## Configuration

To configure the module you need to have an account on our website.  
> If you do not have one, please access [ClaroBi][clarobi] and start your 6 months free trial.  
After you have successfully registered you will receive from ClariBi 2 keys necessary for authentication   
>and data encryption ( `API KEY` and `API SECRET` ) and your `LICENSE KEY`.

After you have all the necessary keys, please follow the steps:      
* In the configuration form, you will need to provide all the data as follows:
    * `Domain`: your shop domain (same as the one provided for registration on our website)
    * `License` key: license key provided by ClaroBi
    * `Api key`: Api key provided by ClaroBi
    * `Api secret`: Api secret provided by ClaroBi
* After all the inputs have been completed, click *Save*.

> You may come back ( *BackOffice >> Modules >> Module Manager >> ClaroBi*) at any time to finish the configuration,  
> but no analytics will be run until everything is setup.    


## Statistics

After the installation the module will start calculate and gather data for analytics.   
Statistics about products visualization and add to carts can be seen by accessing the BackOffice of your shop followed by Stats >> ClaroBi.  
All the information retrieved from your shop can be found by accessing you [ClaroBi account][clarobi-login].

## Uninstall

To uninstall ClaroBi module you need to:
 * Locate it in *BackOffice >> Module >> Module Manager*
 * Selected and choose *Bulk actions >> Uninstall* or in drop-down menu next to *Configure choose >> Uninstall*



[clarobi]: https://clarobi.com/
[clarobi-login]: https://app.clarobi.com/login
[clarobi-repo]:  https://github.com/intelive/clarobi-prestashop
[addons]: https://addons.prestashop.com/en/link/to/clarobi/in/market/place


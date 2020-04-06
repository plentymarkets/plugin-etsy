
# Etsy plugin user guide

<div class="container-toc"></div>


## Registering with Etsy

**Etsy** is an American online market focused on handmade or vintage items. In order to set up Etsy in your plentymarkets system, you have to register as a seller first.


## Installing Etsy in plentymarkets

After buying the Etsy plugin in the plentyMarketplace, install the market in the **Plugins » Plugin Overview** menu. Select the filter **Not installed** or **All** to display plugins that are not installed and to install them.

### Authentication

First, the interface has to be activated in the **Setup » Markets » Etsy » Authentication** menu. Click on **Etsy login** to do so. Afterwards, you are forwarded directly to Etsy where the interface can be activated.

### Setting up Etsy

Go to **Setup » Markets » Etsy » Settings** to activate the item export, stock update and order import.

**Note:** Prior to setting up Etsy in plentymarkets, you have to set the option **Renewal options** to **Manual** in the **Shop-Manager » Listings »** _Open listing_ menu at Etsy. Otherwise, the Etsy plugin will not work correctly.

## Activating the order referrer

In order to link items with Etsy, you have to activate the order referrer Etsy in the **Setup » Orders » Order referrer** menu.

##### Activating the order referrer for Etsy:

1. Go to **Setup » Orders » Order referrer**. 
2. Place a check mark for **Etsy**.  
3. **Save** the settings.


## Setting the item availability

Items have to be available for Etsy. This is done in the **Availability** tab of an item within the **Item » Edit item » Tab: Variation ID** menu.

##### Setting the item availability for Etsy:

1. Go to **Item » Edit item » Open item » Tab: Variation ID » Tab: Settings**. 
2. Activate the main variation in the **Availability** section. 
3. Click on the **Availability** tab. 
4. Click in the **Markets** section in the selection field.<br/>
→ A list with all available markets is displayed. 
5. Activate the option **Etsy**. 
6. Click on **Add**.<br/>
→ The Market is added. 
7. **Save** the settings.<br/>
→ The item is available on Etsy.

The availability for variations can be individually edited in the **Item » Edit item » Open item » Tab: Variation ID » Open variation » Tab: Availability** menu.

If you are already selling on Etsy, save Etsy's listing IDs as SKUs in the **Item » Edit item » Open item » Tab: Variation ID » Tab: Settings »Tab: Availability** menu for the referrer **Etsy**. By doing so, items will be assigned during the item export to the items already listed on Etsy in order to prevent overselling. The Etsy listing ID must be indicated in the following format: **Etsy listing ID-plentymarkets Variation ID**, for example **708093072-3467**.


## Defining a sales price

Proceed as described below to define a sales price for the order referrer Etsy. This price will be displayed on Etsy. 

##### Defining a sales price for Etsy:

1. Go to **Setup » Item » Sales price » Open sales price » Tab: Settings**. 
2. Place a check mark next to the referrer **Etsy**. 
3. **Save** the settings.


## Creating a catalogue

Item data is exported to Etsy via catalogue templates. In order to export your items to Etsy, go to **Data » Catalogs** and create a catalogue template. For further information about catalogues, refer to the [Managing catalogues](https://knowledge.plentymarkets.com/en/data/exporting-data/Managing-catalogs) page of the manual.

Use the **Listing - Etsy** catalogue template for Etsy.

→ **Note**: Note that the menu **Data » Catalogs** only becomes visible after you have installed a plugin which provides a catalogue template.

In order to export your items to Etsy, you have to create a catalogue. Link the Etsy data fields with item data saved in plentymarkets in the catalogue template afterwards. The catalogue template is automatically exported to Etsy once a day if you have activated the **Item export** in the **Setup » Markets » Etsy » Settings** menu.

#### Creating a catalogue:

1. Go to **Data » Catalogs**.
2. Click on **Create catalog**.
3. Enter a name for the catalogue.
4. Select the **Listing - Etsy** catalogue template from the drop-down list.
4. **Save** the settings.<br/>
→ The catalogue is created.<br/>
→ The catalogue is opened for further editing.

After you have created a catalogue, link the market's data fields with item data saved in plentymarkets. For further information about the plentymarkets data sources which can be linked, refer to the [Mapping data sources](https://knowledge.plentymarkets.com/en/data/exporting-data/Managing-catalogs#_mapping_data_sources) chapter.

#### Linking data fields in the catalogue:

1. Go to **Data » Catalogs**.
2. Click on the catalogue that you have created.<br/>
→ The catalogue opens.<br/>
→ The market's data fields are displayed on the left-hand side.<br/>
→ **Tip**: Mandatory fields are marked with an asterisk.
3. Select a plentymarkets data source for the market's data fields from the drop-down list.
4. Click on **Add source** to assign another plentymarkets data source to a data field.<br/>
→ A new line is displayed.<br/>
→ **Note**: Only one value is exported even if you assign more than one data source to a data field. The data is checked in the order of how you assigned it. This means that if the first data field does not provide a value, the second data field is exported etc.
5. Link the desired data fields.<br/>
→ The mandatory fields must be linked to a plentymarkets data source.
6. **Save** the settings.

#### Specifics of the Etsy catalogue template:

The **Shop sections** data field in the **Listing - Etsy** template provides all shop sections that you created at Etsy. Those shop sections can be selected from the drop-down list and be linked to data saved in plentymarkets.


## Updating Etsy categories

In order to update Etsy categories in plentymarkets, delete your credentials in the **Setup » Markets » Etsy » Authentication** menu and add them again.

#### Updating Etsy categories:

1. Go to **Setup » Markets » Etsy » Authentication**.
2. Click on **Delete** to delete the credentials.
3. Click on the globe icon to add new credentials.<br/>
→ You are forwarded to Etsy.
4. Click on **Allow access** at Etsy.<br/>
→ New credentials are created and saved in plentymarkets.<br/>
→ The Etsy categories are updated.


## Transferring legal information

In order to transfer legal information to Etsy, you have to save the information for each language you use for Etsy. The legal information is then added to the item description. 

##### Transferring legal information:

1. Go to **Setup » Markets » Etsy » Legal information**.<br/>
→ The window **Legal information** opens.
2. Select the language for which you want to save legal information.
3. Enter the text.
4. **Save** the settings.


## Automatically sending payment confirmations

Set up an event procedure to automatically send payment confirmations to Etsy when incoming payment is booked.

##### Setting up an event procedure:

1. Go to **Setup » Orders » Event procedures**. 
2. Click on **Add event procedure**.<br/>
→ The **Create new event procedure** window opens. 
3. Enter a name. 
4. Select the Event listed in table 1. 
5. **Save** the settings. 
6. Pay attention to the explanations given in table 1 and carry out the settings as desired. 
7. Place a check mark next to the option **Active**. 
8. **Save** the settings.

|Setting       |Option                                         |Selection |
|:---          |:---                                           |:--- |
|**Event**     |**Payment: Complete**                          | |
|**Filter 1**  |**Order > Order type**                         |**Order** |
|**Filter 2**  |**Order > Referrer**                           |**Etsy** |
|**Procedure** |**Plugin > Send payment confirmation to Etsy** | |

_Table 1: Automatically sending payment confirmations to Etsy_


## Automatically sending shipping confirmations

Set up an event procedure to automatically send shipping confirmations to Etsy when the outgoing items are booked.

##### Setting up an event procedure:

1. Go to **Setup » Orders » Event procedures**. 
2. Click on **Add event procedure**.<br/>
→ The **Create new event procedure** window opens. 
3. Enter a name. 
4. Select the event listed in table 2. 
5. **Save** the settings. 6. Pay attention to the explanations given in table 2 and carry out the settings as desired. 
7. Place a check mark next to the option **Active**. 
8. **Save** the settings.

|Setting       |Option                                          |Selection |
|:---          |:---                                            |:--- |
|**Event**     |**Order change: Outgoing items booked**         | |
|**Filter 1**  |**Order > Order type**                          |**Order** |
|**Filter 2**  |**Order > Referrer**                            |**Etsy** |
|**Procedure** |**Plugin > Send shipping confirmation to Etsy** | |

_Table 2: Automatically sending shipping confirmations to Etsy_


## Assigning rights

Users with **Back end** access need rights in order to use the **Etsy plugin**. Open the **Setup » Settings » User » Rights » User** menu to assign rights.

 #### Assigning rights for back end users:
 
 1. Go to **Setup » Settings » User » Rights » User**.
 2. Use the search function and open the user that you would like to edit.
 3. Expand the **Authorisations: Markets** menu.
 4. Select the setting **Credentials**.
 5. **Save** the settings.


## License

This project is licensed under the GNU AFFERO GENERAL PUBLIC LICENSE.- find further information in the [LICENSE.md](https://github.com/plentymarkets/plugin-etsy/blob/master/LICENSE.md).


## Information

The term 'Etsy' is a trademark of Etsy, Inc. This application uses the Etsy API but is not endorsed or certified by Etsy, Inc.

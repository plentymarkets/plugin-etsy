
# Etsy plugin user guide

<div class="container-toc"></div>

## Registering with Etsy

**Etsy** is an American online market focused on handmade or vintage items. In order to set up Etsy in your plentymarkets system, you have to register as a seller first an create and new [App](https://www.etsy.com/developers/documentation/getting_started/register). You will then receive the access data that you need for the configuration in plentymarkets.

## Installing Etsy in plentymarkets

After buying the Etsy plugin in the plentyMarketplace, install the market in the menu **Plugins » Plugin Overview**. Select the filter **Not installed** or **All** to display plugins that are not installed and to install them. After having installed **Etsy**, open the plugin. Now enter the Keystring and Shared secret in the **Configuration** section. You should have received both after creating the app on Etsy. Save the setting.

### Granting rights

First, the interface has to be activated in the menu **System » System settings » Markets » Etsy » Authentication**. Click on **Etsy login** to do so. Afterwards, you are forwarded directly to Etsy where the interface can be activated.

### Setting up Etsy

Go to **System » System settings » Markets » Etsy » Settings** to enter your Store ID and to activate the item export, stock update and order import. Etsy allows 500 calls per day. If you need more than 500 calls, contact Etsy directly.

## Activating the order referrer

In order to link items, properties etc. with Etsy you will have to activate the order referrer Etsy in the menu **System » System settings » Orders » Order referrer**.

##### Activating the order referrer for Etsy:

1. Open the **System » System settings » Orders » Order referrer** menu. 
2. Place a check mark for **Etsy**.  
3. **Save** the settings.

## Setting the item availability

Items have to be available for Etsy. This is done in the **Availability** tab of an item within the **Item » Edit item » Tab: Variation ID** menu. It is not possible to transfer variations to Etsy. Therefore, item variations are transferred as main items to Etsy.

##### Setting the item availability for Etsy:

1. Open the **Item » Edit item » Open item » Tab: Variation ID » Tab: Settings** menu. 
2. Activate the main variation in the **Availability** section. 
3. Click on the **Availability** tab. 
4. Click in the **Markets** section in the selection field. → A list with all available markets will be displayed. 
5. Activate the option **Etsy**. 
6. Click on **Add**. → The Market will be added. 
7. **Save** the settings. → The item is available on etsy.

The availability for variations can be individually edit in the **Item » Edit item » Open item » Tab: Variation ID » Open variation » Tab: Availability** menu.

If you are already selling on Etsy, save Etsy's listing IDs as SKUs in the menu **Item » Edit item » Open item » Tab: Variation ID » Tab: Settings »Tab: Availability** for the referrer **Etsy**. By doing so, items will be assigned during the item export to the items already listed on Etsy in order to prevent overselling.

## Defining a sales price

Proceed as described below to define a sales price for the order referrer Etsy. This price will be displayed on Etsy. 

##### Defining a sales price for Etsy:

1. Open the menu **System » System settings » Item » Sales price » Open sales price » Tab: Settings**. 
2. Place a check mark next to the referrer **Etsy**. 
3. **Save** the settings.

## Creating a catalogue

Item data is exported to Etsy via catalogue templates. In order to export your items to Etsy, go to **Data » Catalogs** and create a catalogue template. For further information about catalogues, refer to the [Managing catalogues](https://knowledge.plentymarkets.com/en/data/exporting-data/Managing-catalogs) page of the manual.

→ **Note**: Note that the menu **Data » Catalogs** only becomes visible after you have installed a plugin which provides a catalogue template.

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

## Updating Etsy categories

In order to update Etsy categories in plentymarkets, delete your credentials in the **System » System settings » Markets » Etsy » Authentication** menu and add them again.

#### Updating Etsy categories:

1. Open the **System » System settings » Markets » Etsy » Authentication** menu.
2. Click on **Delete** to delete the credentials.
3. Click on the globe icon to add new credentials.<br/>
→ You are forwarded to Etsy.
4. Click on **Allow access** at Etsy.<br/>
→ New credentials are created and saved in plentymarkets.<br/>
→ The Etsy categories are updated.

## Transferring legal information

In order to transfer legal information to Etsy, you have to save the information for each language you use for Etsy. The legal information is then added to the item description. 

##### Transferring legal information:

1. Go to **System » System settings » Markets » Etsy » Legal information**
-> The window **Legal information** opens.
2. Select the language for which you want to save legal information.
3. Enter the text.
4. **Save** the settings.

## Automatically sending payment confirmations

Set up an event procedure to automatically send payment confirmations to Etsy when incoming payment is booked.

##### Setting up an event procedure:

1. Go to **System » System settings » Orders » Event procedures**. 
2. Click on **Add event procedure**. → The **Create new event procedure** window will open. 
3. Enter a name. 
4. Select the Event listed in table 1. 
5. **Save** the settings. 
6. Pay attention to the explanations given in table 1 and carry out the settings as desired. 
7. Place a check mark next to the option **Active**. 
8. **Save** the settings.

<table>
	<thead>
		<th>
			Setting
		</th>
		<th>
			Option
		</th>
<th>
			Selection
		</th>
	</thead>
	<tbody>
      <tr>
         <td><strong>Event</strong></td>
         <td><strong>Payment: Complete</strong></td> 
<td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Order &gt; Order type</strong></td>
<td><strong>Order</strong></td>
      </tr>
<tr>
         <td><strong>Filter 2</strong></td>
         <td><strong>Order &gt; Referrer</strong></td>
<td><strong>Etsy</strong></td>
      </tr>
      <tr>
         <td><strong>Procedure</strong></td>
         <td><strong>Plugin &gt; Send payment confirmation to Etsy</strong></td>
<td>&nbsp;</td>
      </tr>
</tbody>
	<caption>
		Table 1: Event procedure for sending automatic payment confirmations to Etsy
	</caption>
</table>

## Automatically sending shipping confirmations

Set up an event procedure to automatically send shipping confirmations to Etsy when the outgoing items are booked.

##### Setting up an event procedure:

1. Go to **System » System settings » Orders » Event procedures**. 
2. Click on **Add event procedure**. → The **Create new event procedure** window will open. 
3. Enter a name. 
4. Select the event listed in table 2. 
5. **Save** the settings. 6. Pay attention to the explanations given in table 2 and carry out the settings as desired. 
7. Place a check mark next to the option **Active**. 
8. **Save** the settings.


<table>
	<thead>
		<th>
			Setting
		</th>
		<th>
			Option
		</th>
<th>
			Selection
		</th>
	</thead>
	<tbody>
      <tr>
         <td><strong>Event</strong></td>
         <td><strong>Order change: Outgoing items booked</strong></td> 
<td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Order &gt; Order type</strong></td>
<td><strong>Order</strong></td>
      </tr>
<tr>
         <td><strong>Filter 2</strong></td>
         <td><strong>Order &gt; Referrer</strong></td>
<td><strong>Etsy</strong></td>
      </tr>
      <tr>
         <td><strong>Procedure</strong></td>
         <td><strong>Plugin &gt; Send shipping confirmation to Etsy</strong></td>
<td>&nbsp;</td>
      </tr>
</tbody>
	<caption>
		Table 2: Event procedure for sending automatic shipping confirmations to Etsy
	</caption>
</table>

## Overview of API-Calls

<table>
<thead>
		<th>
			Process
		</th>
		<th>
			Call
		</th>
	</thead>
	<tbody>
      <tr>
         <td><b>Listing start</b></td>
         <td>One call per language. No further call is needed if only one language exists.<br /> One call for the method <b>CreateListing</b>.<br /> One call per item image.<br /> One call for the method <b>Publish</b>.<br /> => at least three API-Calls</td> 
      </tr>
      <tr>
         <td><b>Listing update</b></td>
         <td>One call per language.<br /> => at least one API-Call</td>
      </tr>
<tr>
         <td><b>Stock update</b></td>
         <td>One call per listing.</td>
      </tr>
      <tr>
         <td><b>Delete listing</b></td>
         <td>One call per listing.</td>
      </tr>
      <tr>
         <td><b>Order import</b></td>
         <td>One call every hour.</td>
      </tr>
</tbody>
</table>

## Required rights for the Etsy plugin
    
Users with **Backend** access need REST-API rights to use the **Etsy plugin**.

Use the **System » System settings » Settings » User » Rights » User** menu to assign rights.
    
#### Required REST-API rights
    
The required REST-API rights for users with **Backend** access are listed below.
    
 - Item » **Category** and all subordinate rights
 - Items » **Properties** and all subordinate rights
 - Markets » **Credentials** and all subordinate rights
    
#### Assigning REST-API rights:
    
1. Go to **System » System settings » Settings » User » Rights » User**.
2. Click on the **user** that you want to assign rights to.
3. In the section **Authorisations**, place a check mark next to the required rights.
4. **Save** the settings.

## License

This project is licensed under the GNU AFFERO GENERAL PUBLIC LICENSE.- find further information in the [LICENSE.md](https://github.com/plentymarkets/plugin-etsy/blob/master/LICENSE.md).

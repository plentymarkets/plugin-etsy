
# Etsy plugin user guide

<div class="container-toc"></div>

## Registering with Etsy

**Etsy** is an American online market focused on handmade or vintage items.

In order to use the Etsy plugin, you need to register as a seller first.

## Installing Etsy in plentymarkets

After buying the Etsy plugin in the plentyMarketplace, install the market in the menu **Plugins » Plugin Overview**. Select the filter **Not installed** or **All** to display plugins that are not installed and to install them.

### Granting rights

First, the interface has to be activated in the menu **System » System settings » Markets » Etsy » Authentication**. Click on **Etsy login** to do so. Afterwards, you are forwarded directly to Etsy where the interface can be activated.

### Setting up Etsy

Go to **System » System settings » Markets » Etsy » Settings** to enter your Store ID and to activate the item export, stock update and order import. Etsy allows 500 calls per day. If you need more than 500 calls, contact Etsy directly.

## Activating the order referrer

In order to link items, properties etc. with Etsy you will have to activate the order referrer Etsy in the menu **Settings » Orders » Order referrer**.

##### Activating the order referrer for Etsy:

1. Open the **Settings » Orders » Order referrer** menu. 
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

1. Open the menu **Settings » Item » Sales price » Open sales price » Tab: Settings**. 
2. Place a check mark next to the referrer **Etsy**. 
3. **Save** the settings.

## Link categories

Link your store categories to the Etsy categories in order to display your items in these categories. Further items from the linked category will be assigned automatically.

##### Linking categories:

1. Go to **Settings » Markets » Etsy » Category link**. 
2. Click on **Search**. → The **Select category** window will open. 
3. Select the Etsy category that best matches your online store category. 
4. Click on **Apply**. → The name and category path of the Etsy category will be displayed. 
5. If you know the name of the Etsy category, enter it directly into the **Market category** field to link it to your online store category. 
6. **Save** the settings.

## Linking properties

In order to use properties for the Etsy market, these properties have to be linked with Etsy. Please note that the properties **Who made** and **When made** are mandatory for the item export.

##### Linking properties:

1. Go to **Settings » Markets » Etsy » Property link**. 
2. Click on **Search**. → The **Select property** window will open. 
3. Select the Etsy property that best matches to your online store property. 
4. Click on **Apply**. → The name and property path of the Etsy property will be displayed. 
5. **Save** the settings.

## Transferring legal information

In order to transfer legal information to Etsy, you have to save the information for each language you use for Etsy. The legal information is then added to the item description. 

##### Transferring legal information:

1. Go to **Settings » Markets » Etsy » Legal information**
-> The window **Legal information** opens.
2. Select the language for which you want to save legal information.
3. Enter the text.
4. **Save** the settings.

## Linking shipping profiles

Link the shipping profiles that you created on Etsy to the shipping profiles of your online store in the menu **Settings » Markets » Etsy » Shipping profile links**. 

##### Linking shipping profiles:

1. Go to **Settings » Markets » Etsy » Shipping profile links**. 
2. Select the Etsy shipping profile that best matches to your online store shipping profile. 
3. Click on **Apply**. 
4. **Save** the settings.

## Automatically sending payment confirmations

Set up an event procedure to automatically send payment confirmations to Etsy when incoming payment is booked.

##### Setting up an event procedure:

1. Go to **Settings » Orders » Event procedures**. 
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

## Automatically send shipping confirmation

Set up an event procedure to automatically send shipping confirmations to Etsy when the outgoing items are booked.

##### Setting up an event procedure:

1. Go to **Settings » Orders » Event procedures**. 
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

## Overview API-Calls

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
    
Users of the user class **Variable** need REST-API rights to use the **Etsy plugin**.

Use the **System » Settings » User » Accounts » User account » Tab: Right** menu to assign rights.

→ **Tip**: Use the filter function to search for users of the user class **Variable**. To do so, go to **Tab: Filter** and select the filter **Variable** within the drop-down list **Class**. Click on **Search**.
    
#### Required REST-API rights
    
The required REST-API rights for variable user classes are listed below.
    
 - **Categories** and all subordinate rights
 - Items » **Properties** and all subordinate rights
 - Markets » **Credentials** and all subordinate rights
    
#### Assigning REST-API rights
    
1. Go to **System » Settings » User » Accounts**.
2. Click on the **user** that you want to assign rights to.
3. Go to **Tab: Right » Tab: REST-API**.
4. Place a check mark next to the required rights.
5. **Save** the settings.

## License

This project is licensed under the GNU AFFERO GENERAL PUBLIC LICENSE.- find further information in the [LICENSE.md](https://github.com/plentymarkets/plugin-etsy/blob/master/LICENSE.md).

## Information

The term 'Etsy' is a trademark of Etsy, Inc. This application uses the Etsy API but is not endorsed or certified by Etsy, Inc.

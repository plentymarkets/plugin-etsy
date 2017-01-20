

# Etsy plugin Userguide

<div class="container-toc"></div>

## Registering with Etsy

**Etsy** is an e-commerce website focused on handmade, vintage and unique factory-manufactured items. You need to register with Etsy before setting up the Etsy plugin in plentymarkets. You will then receive the access data that you need for the configuration in plentymarkets.
## Getting started and requirements

In this step, you have to clone the Git repository into your plentymarkets inbox. You will need the remote URL from GitHub as well as your login details. 

1. Go to **Start » Plugins**.
2. Click on **Add plugin**.
→ The **New plugin** window will open. .
3. Click on **Git**.
→ The **Settings** window will open.
4. Enter the remote URL.
→ You can copy the URL by clicking on **Clone or download** in the repository on GitHub. 
5. Enter your user name and password.
6. Click on **Test connection**.
→ Connectivity to the Git repository is checked and established and the drop-down menu **Branch** can be selected. 
7. Select the branch of the repository that you want to clone and edit.
8. **Save** the settings.
→ The plugin repository is cloned to the plentymarkets inbox and the plugin is added to the plugin list. 


## Setting up Etsy in plentymarkets

Once you have registered with Allyouneed, set up the market in plentymarkets. To do so, proceed as follows.

## Setting the item availability

Items have to be available for Etsy. This is done in the **Availability** tab of an item within the **Item » Edit item » Tab: Variation ID** menu.

##### Setting the item availability for Etsy:

1. Open the **Item » Edit item » Open item » Tab: Variation ID » Tab: Settings** menu.
2. Activate the main variation in the **Availability** section.
3. Click on **Availability** tab.
4. Click in the **Markets** section in the selection field. 
    → A list with all available markets will be displayed.
5. Activate the option **Etsy**.
6. Click on **Add**.
    → The market will be added.
7. **Save** the settings.
    → The item is available on Etsy.

The availability for variations can be individually edit in the **Item » Edit item » Open item » Tab: Variation ID » Open Variante »Tab: Availability** menu.

## Defining a sales price

Proceed as described below to define a sales price for the order referrer Etsy. This price will be displayed on Etsy.  

##### Defining a sales price for Etsy:

1. Open the menu **Settings » Item » Sales price » Open sales price » Tab: Settings**.
2. Place a check mark next to the referrer **Etsy**.
3. **Save** the settings.

## Linking categories

Link your online store categories to the Etsy categories in order to display your items in these categories. Further items from the linked category will be assigned automatically.

##### Linking categories:

1. Got to **Settings » Markets » Etsy » Category link**.
2. Click on **Search**.
    → The **Select category** window will open.
3. Select the Etsy category that best matches your online store category.
4. Click on **Apply**.
    → The ID and category path of the Etsy category will be displayed.
5. If you already know the ID of the Etsy category, enter it directly into the **Market category** field to link it to your online store category.
6. **Save** the settings.

## Linking properties

Link your online store properties to the Etsy properties.

##### Linking properties:

1. Got to **Settings » Markets » Etsy » Property link**.
2. Click on **Search**.
    → The **Select property** window will open.
3. Select the Etsy property that best matches your online store property.
4. Click on **Apply**.
    →The name and property path of the Etsy property will be displayed.
5. **Save** The settings.

## Linking shipping profiles

Go to **Settings » Markets » Etsy » Shipping profile link** to link the online store shipping profiles with Etsy shipping profiles. 

##### Linking shipping profiles:

1. Go to **Settings » Markets » Etsy » Shipping profile link**.
2. Select the Etsy shipping profile that best matches your online store shipping profile.
3. Click on **Apply**.
4. **Save** The settings.

## Automatically sending payment confirmations

Set up an event procedure to automatically send payment confirmations to Etsy.

##### Setting up an event procedure:

1. Got to **Settings » Orders » Event procedures**.
2. Click on **Add event procedure**.
→ The **Create new event procedure** window will open.
3. Enter name.
4. Select the event listed in table 1.
5. **Save** the settings.Pay attention to the explanations given in table 1 and carry out the settings.
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
         <td><strong>Order > Order type</strong></td>
<td><strong>Order</strong></td>
      </tr>
<tr>
         <td><strong>Filter 2</strong></td>
         <td><strong>Order > Referrer</strong></td>
<td><strong>Etsy</strong></td>
      </tr>
      <tr>
         <td><strong>Procedure</strong></td>
         <td><strong>Plugin > Send payment confirmation to Etsy</strong></td>
<td>&nbsp;</td>
      </tr>
</tbody>
	<caption>
		Table 2: event procedure for sending automatic payment confirmations to Etsy
	</caption>
</table>

## Automatically sending shipping confirmation

Set up an event procedure to automatically send shipping confirmations to Etsy when the outgoing items are booked.

##### Setting up an event procedure:

1. Got to **Settings » Orders » Event procedures**.
2. Click on **Add event procedure**.
→ The **Create new event procedure** window will open.
3. Enter name.
4. Select the event listed in table 2.
5. **Save** the settings.
6. Pay attention to the explanations given in table 2 and carry out the settings as desired.
7. Place a check mark next to the option **Active**.
8. **Save** the settings.


<table>
	<thead>
		<th>
			Settings
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
         <td><strong>Order > Order type</strong></td>
<td><strong>Order</strong></td>
      </tr>
<tr>
         <td><strong>Filter 2</strong></td>
         <td><strong>Order > Referrer</strong></td>
<td><strong>Etsy</strong></td>
      </tr>
      <tr>
         <td><strong>Procedure</strong></td>
         <td><strong>Plugin > Send shipping confirmation to Etsy</strong></td>
<td>&nbsp;</td>
      </tr>
</tbody>
	<caption>
		Table 2: event procedure for sending automatic shipping confirmations to Etsy
	</caption>
</table>

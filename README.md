# business-in-touch Project

business-in-touch is a web application built with php and mysql  for managing small stores

# Project Description
The main idea of the project is to have a simple application working on a web server and can be accessed from any smartphone makes you able to manage sales and purchasing and inventory
# application main sections
# 1- users
holds application users and their credentials
you can add new users from hear by pressing the add button at the bottom of the display area
or you can edit an existing one by clicking the edit button attached to its card
or you can delete an existing one by clicking the delete button attached to its card

# 2- customers
holds the customers and their informations like name, code, address, phone etc and the current balance which will be updated with each sales action
--just like the above you can add or edit or delete 

# 3- suppliers
holds the suppliers and their informations like name, code, address, phone etc and the current balance which will be updated with each purchase action
--just like the above you can add or edit or delete 

# 4- units
this sections holds the names of the units used in products section like (pack, gram, kilogram etc) without any link between them
--just like the above you can add or edit or delete 

# 5- products
this section holds the products information like (name, code, image )
product units depends on the units names inserted in units section
you can add unlimited units to each product specific its content of the main unit and the purchase price and sell price
--just like the above you can add or edit or delete 

# 6- sales
each sales action contains basic information like 
- customer (select from customer inserted in customer section)
- date 
- serial (automated serial number)
- products list (you can add unlimited product to the order usin the product name or product code)
--by selecting the product application will ask for the unit (default for sales will by selected)
--by selecting the unit application will ask quanity (default is 1)
-- based of the product and unit and quantity the total price of the product will be calculated automatically
- order total (summation of all products totals)
- payment option (paid or not paid)
just like the above you can add or edit or delete 
# 7- purchases
each purchase action contains basic information like 
- supplier (select from suppliers inserted in supplier section)
- date 
- serial (automated serial number)
- products list (you can add unlimited product to the order usin the product name or product code)
--by selecting the product application will ask for the unit (default for purchase will by selected)
--by selecting the unit application will ask quanity (default is 1)
-- based of the product and unit and quantity the total price of the product will be calculated automatically
- order total (summation of all products totals)
- payment option (paid or not paid)
just like the above you can add or edit or delete 

# 8- payments
each sales action contains basic information like 
- date
- serial
- customer or supplier 
- amount
-just like the above you can add or edit or delete 
# 9- income
each sales action contains basic information like 
- date
- serial
- customer or supplier 
- amount
-just like the above you can add or edit or delete 

# 10- reports
this section holds all printable reports like 
- customer balance
- supplier balance
- product card
- products amounts


# Installation
1- create  a new folder (business-in-touch)
2- copy all files to the new folder
3- create new database (business-in-touch)
4- restore file (db.sql) in the root folder to create database tables
5- edit database credentials in file db.php



# Using the application
open your browser and navigate to http://yourserver/business-in-touch
-notes-
replace : your server with the domain name of yours
replace : traking with the new forder name in my case (business-in-touch)
enter user name and password to login 
default 
user :admin 
password: admin

1- navigating through the application sections
click on the menu button on the header of the application to show the main application menu
you can open any section by clicking its icon 

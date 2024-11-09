import requests
import json

# Step 1: Fetch the data from the API
api_url = "https://villagecinemas.com.au/api/cinema/getCinemasV1Extended"
response = requests.get(api_url)
data = response.json()

# Step 2: Parse the JSON response
items = data.get("Items", [])

# build link

# Step 3: Format the data into the desired PHP structure
php_content = "<?php\n$venues = [\n"
for item in items:
    link = f"https://villagecinemas.com.au/{item['PageUrl']}"
    
    content_cin = item['Description']
    
    content_cin = item['Description'].replace("'", "\\'").replace('"', '\\"')

    if item['ParkingInfo'] != "":
        content_cin += "\n\n" + item['ParkingInfo'].replace("'", "\\'").replace('"', '\\"')

    if item['PublicTransport'] != "":
        content_cin += "\n\n" + item['PublicTransport'].replace("'", "\\'").replace('"', '\\"')

    if item['PhoneNumber'] != "":
        content_cin += "\n\n" + 'Call on ' + item['PhoneNumber'].replace("'", "\\'").replace('"', '\\"')
    
    php_content += "    (object) [\n"
    php_content += f"        'id' => '{item['CinemaId']}',\n"
    php_content += f"        'suburb' => '{item['City']}',\n"
    php_content += f"        'Venue Name' => '{item['DisplayName']}',\n"
    php_content += f"        'Address' => '{item['Address1']}',\n"
    php_content += f"        'state' => '{item['State']}',\n"
    php_content += f"        'link' => '{link}',\n"
    php_content += f"        'content' => '{content_cin}',\n"
    php_content += "    ],\n"
php_content += "];\n"

# Step 4: Write the formatted data to a PHP file
with open("village-venues.php", "w") as file:
    file.write(php_content)

print("PHP file 'venues.php' has been created.")
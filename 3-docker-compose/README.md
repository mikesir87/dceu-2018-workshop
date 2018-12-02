# Docker Compose Hands-on

In the workshop, we talked about creating a basic multi-service application. But, the final compose file wasn't _quite_ ready to run an application. While both the app and database would launch, there's no app code to connect to it and the application doesn't have the necessary dependencies installed to use MySQL. So, let's fix all of this!

## Setup

1. In a new PWD instance, clone this repo:

    ```
    git clone https://github.com/mikesir87/dceu-2018-workshop.git
    cd dceu-2018-workshop
    ```

    We will be using this as the starting point for our workshop.

## Replacing the App Image

As mentioned in the overview, the base `php:7-apache` image doesn't have the necessary MySQL extensions installed. To fix this, we will create our own image and update our compose file to use it!

1. In the workshop directory, create a new `Dockerfile`. In it, place the following:

    ```
    FROM php:7-apache
    RUN docker-php-ext-install mysqli
    ```

    The `php` image includes a few helper scripts to install extensions. We're simply using that script to install the extension.

2. Update the compose file to use this image to add the `build` instruction:

    ```
    services:
      app:
        build: ./
    ```

    The `build` instruction tells Compose to build an image using the Dockerfile found in the current directory (`./`) and then use that as the image.

3. Remove the `image` declaration from the `app` service.

4. Now, if you run `docker-compose up`, you should see an image be created.


## Connecting to the Database

If you look in the `src/index.php` file, you'll see some connection details at the top of the file. In order for the app to work, we need to get an actual connection to the database.

When Docker starts a container, it makes itself the DNS resolver for all requests (DNS is used to lookup an IP address for a hostname). While many applications may connect to a database using an IP address, DNS is a _fantastic_ way to do service discovery (finding out where things are).

1. Let's get into the PHP container and see if it can find the database.

   
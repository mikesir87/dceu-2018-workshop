# Hands-on #1

In this first hands-on exercise, we're going to build our first container image, push it to Docker Hub, and then run a container using that image.

**If you haven't done so yet**, [](create a Docker Hub account). You will need this to access Play with Docker and sharing images through Docker Hub.

## Getting Started

For the workshop, we will be using Play with Docker, a tool created by several Docker Captains to help you get up and running. Beyond it being a great tool, we don't have to rely on the conference's network speeds, as the containers and images are running remotely.

1. Open your web browser to [https://play-with-docker.com](https://play-with-docker.com).
2. Login using your Docker Hub account. If you don't have an account yet, [INSERT](you can do so here).


## Creating and running our first Image

As we discussed, images are best created and maintained using Dockerfiles. These files are simple text files that provide instructions on how to create a container image.

1. In the PWD console, create a folder named `exercise1`.
2. In the `exercise1` directory, create a file named `index.php`. In that file, we're going to create the simplest PHP application:

    ```php
    <?php phpinfo();
    ```

3. Now, create a file named `Dockerfile`. In the file, put the following:

    ```
    FROM php:7-apache
    COPY index.php /var/www/html
    ```

   - The `FROM` command tells the builder that our container image will base from the `php:7-apache` image. Any instructions we perform afterwards will add additional layers.
   - The `COPY` command causes the local `index.php` file to be placed into the file system we are creating for our container image at `/var/www/html`.

4. At this point, let's build our image! Run the following command:

    ```
    docker build -t my-first-php-image .
    ```

    - The `-t` flag indicates a 'tag', or a name that we want to apply to the image. We can then use that to start a container from that name (which we'll do next).
    - The trailing `.` tells the Docker Engine that the Dockerfile and build context to be used is the current directory. The build context indicates the root of files we will use in the Dockerfile (like the copying of the index.php file).

5. Let's run our container image! Run the following command:

    ```
    docker container run -p 80:80 -d my-first-php-image
    ```

    - The `-p` flag tells the Docker Engine that we want to create a mapping of port 80 on the host (the left-side of the argument) to port 80 of the container (the right side). 
    - The `-d` flag tells the Docker Engine that we want to run the new container in 'detached mode'. In other words, simply run it in the background.

6. At this point, you should see a small badge appear at the top of the page with the number '80' in it. Clicking this badge will open a URL to port 80 for your container.

Congrats! You've now created and run your first container image!


## Sharing the Image

The image you just created exists only on the machine that performed the build. So, if we want to share the image, we need to push it to a registry. Think of a registry as a code repo. It's sole purpose is to share container images.

1. In order to push to Docker Hub, we need to authenticate. Do this by running:

    ```
    docker login
    ```

2. All images (except Official Images) in Docker Hub are namespaced. For example, if I push images, they aren't going to simply named `my-first-php-image`, as it doesn't convey _who_ it comes from, but creates a nightmare trying to ensure my image doesn't collide with yours. To fix this, my image would be named `mikesir87/my-first-php-image`. Using the `docker tag` command, we can provide another name for the image we built earlier.

    ```
    # Replace mikesir87 with your Docker Hub username
    docker tag my-first-php-image mikesir87/my-first-php-image
    ```

3. Now that we're authenticated and we have our image tagged correctly, let's push it!

    ```
    # Replace mikesir87 with your Docker Hub username
    docker push mikesir87/my-first-php-image
    ```

4. Open your browser to your repo and you should see the new image!


## Running our Image

With PWD, it's easy to get a new instance. With it being a new instance, the only way our app can run is if we pull the image.

1. Click the 'Create Instance' button in the left navigation menu.
2. In the terminal for our new instance, run the following:

    ```
    # Replace mikesir87 with your Docker Hub username
    docker container run -p 80:80 -d mikesir87/my-first-php-image
    ```

    We'll see the image get pulled from Docker Hub and start up. Magic, huh?

3. Click on the '80' badge and see the app!


## Wrap-up

While this was a pretty simple application, let's think about what was needed to run the application. 

- We needed a PHP runtime engine, an Apache HTTP server, and a PHP script. Before containers, we would have needed to have a machine that had the correct versions installed and make the script available. But, **our container image shipped everything it needed to run.**
- We created our container image using a Dockerfile, which assures our application is consistent no matter where we build it. With it being a text file, the full environment of our application can be version controlled!

When you're done, place the Post-It note on the back of your laptop monitor.

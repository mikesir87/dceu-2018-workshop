# Hands-on #2

In this hands-on demo, we're going to do a little bit of a deep-dive into container images and learn a valuable lesson in the process!

## Overview

We have come across a "mystery" image that we're not too familiar with what it is or what's contained. Before running it, we want to find out what's in the image and if there are potentially any issues with it.


## Looking at the image history

Every image is composed of immutable layers. When each layer is created, the executing instruction is recorded and saved. Let's take a look at our mystery image and see what we can figure out just from the history.

1. Remove the PWD instances you already created and create a new instance (get a fresh start).
2. Run `docker image history mikesir87/mystery-image`. This will pull the image and then display all of the layers, as well as the command.
3. One thing you'll notice as that the instructions are truncated. By adding the `—no-trunc` flag (strange how the flag to not truncate is truncated, right?), we can see the full output.

    ```
    docker image history mikesir87/mystery-image
    ```

    If we look at the output, we see several references to npm, node, and others. So, it looks like we're looking at a Node app of some sort. Cool!


## Diving into the Image

When Docker pulls an image, each layer is pulled individually. However, there may be times in which you want to export or save an image to move it somewhere else (like an airgapped network). Fortunately, we can use this same approach to dive into each of the layers.

1. In your instance, let's save our image. When you run the command, the outcome is a stream of a tarball (similar to a ZIP file) containing the image. We could save the tar to ship around, but in our case, we will extract it immediately.

    ```
    docker image save mikesir87/mystery-image | tar -x
    ```

    Run `ls` to look at the contents. We'll see lots of folders with super long names, a `manifest.json`, and a INSERT.

2. Since we're going to be looking at several JSON objects, let's install the `jq` utility.

    ```
    apt-get update && apt-get install -y jq
    ```

3. Now, let's look at the `layers.json`. Run the following command:

    ```
    jq layers.json
    ```

    Looking at the output, we'll see several keys. If we look at the `layers` key, we'll see file paths to various tar files. Each of these contains the file system changes that occurred while that layer was being built. Let's take a look at one.

4. Let's take a look at layer INSERT. Judging by the image history and the order of the layers in the `layers.json`, this should be the layer that's copying lots of files in. Let's take a look!

    ```
    cd INSERT_LAYER_ID
    tar xvf layer.tar
    ```

    Looking at the output, we see lots of files being copied in. Cool! One file that _might_ be of interest could be the `app/settings.js` file. Let's take a look at that.

    ```
    cat app/settings.js
    ```

    **Oh no!** This looks like something that shouldn't have been in the image! Is it in the final image? If we run `docker container run -it mikesir87/mystery-image ls /app`, we see the `app.js` file. But, we don't see the `settings.js` file. What gives?


## Handling file deletions

Remember that image layers are immutable. Once created, the layer is never changed. So, how are file removals represented in the layer's tar?
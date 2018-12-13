# Hands-on #2

In this hands-on demo, we're going to do a little bit of a deep-dive into container images and learn a valuable lesson in the process!

## Overview

We have come across a "mystery" image that we're not too familiar with what it is or what's contained. Before running it, we want to find out what's in the image and if there are potentially any issues with it.


## Looking at the image history

Every image is composed of immutable layers. When each layer is created, the executing instruction is recorded and saved. Let's take a look at our mystery image and see what we can figure out just from the history.

1. Remove the PWD instances you already created and create a new instance (get a fresh start).
2. Run `docker pull mikesir87/mystery-image`. This will pull the image that we are going to use.
3. Run `docker image history mikesir87/mystery-image`. This display all of the layers, as well as the command.
3. One thing you'll notice as that the instructions are truncated. By adding the `--no-trunc` flag (strange how the flag to not truncate is truncated, right?), we can see the full output.

    ```
    docker image history --no-trunc mikesir87/mystery-image
    ```

    If we look at the output, we see several references to npm, node, and others. So, it looks like we're looking at a Node app of some sort. Cool!


## Diving into the Image

When Docker pulls an image, each layer is pulled individually. However, there may be times in which you want to export or save an image to move it somewhere else (like an airgapped network). Fortunately, we can use this same approach to dive into each of the layers.

1. In your instance, let's save our image. When you run the command, the outcome is a stream of a tarball (similar to a ZIP file) containing the image. We could save the tar to ship around, but in our case, we will extract it immediately.

    ```
    docker image save mikesir87/mystery-image | tar -x
    ```

    Run `ls` to look at the contents. We'll see lots of folders with super long names, a `manifest.json`, and a few other metadata files.

2. Since we're going to be looking at several JSON objects, let's install the `jq` utility.

    ```
    apk add --update jq
    ```

3. Now, let's look at the `manifest.json`. Run the following command:

    ```
    jq . manifest.json
    ```

    Looking at the output, we'll see several keys. If we look at the `layers` key, we'll see file paths to various tar files. Each of these contains the file system changes that occurred while that layer was being built. Let's take a look at one.

4. Let's take a look at layer starting with `6a6d515f`. Judging by the image history and the order of the layers in the `manifest.json`, this should be the layer that's copying lots of files in. Let's take a look!

    ```
    cd 6a6d515f9f7dec5f2a0a705bf7640376132e7d78773fc96faae5990ffd543bc5
    tar xvf layer.tar
    ```

    Looking at the output, we see lots of files being copied in. Cool! One file that _might_ be of interest could be the `app/src/settings.js` file. Let's take a look at that.

    ```
    cat app/src/settings.js
    ```

    **Oh no!** This looks like something that shouldn't have been in the image! You've captured the flag!!!


## Handling file deletions

Now that you've captured the flag, let's see if the details are in the final image. If we run `docker container run -it mikesir87/mystery-image ls /app/src`, we see the `index.js` file. But, we don't see the `settings.js` file. What gives?

Remember that image layers are immutable. Once created, the layer is never changed. So, how are file removals represented in the layer's tar? Let's take a look.

1. The final layer is the one in which the `/app/src/settings.js` file is being removed (remember from the image history output earlier?). Get its id from the `manifest.json`

    ```
    jq . manifest.json
    ```

2. With the layer, let's output the contents of the tar, but display only files containing the name `settings`

    ```
    tar tvf [LAYER_ID]/layer.tar | grep settings
    ```

What you should see is a file named `/app/src/.wh.settings.js`. This is the whiteout file we talked about earlier. When the layers are merged together, this file tells the unioned filesystem to ignore the file found in a previous layer.


## Takeaways

- **NEVER** copy credentials into an image. Even if they are deleted, you're still shipping them around and then can still be extracted.
- Clean up your image as you go. Even if you delete files (uninstall packages as an example), you are still shipping them in the original layer.

# README

## Description
The Fork CMS Compression module let's you compress PNG & JPG images on your website. Use this module to shrink images on your site so they will use **less bandwidth and your website will load faster**. The compression module uses the free API of [TinyPNG](https://tinypng.com/) and [TinyJPG](https://tinyjpg.com/).

## Preview
Backend + statistics:

[ ![Image](http://i.imgur.com/ZRE1LX8m.png "Backend") ](http://i.imgur.com/ZRE1LX8.png)   
[ ![Image](http://i.imgur.com/neSqanxm.png "Statistics") ](http://i.imgur.com/neSqanx.png) 

You can see an example/walkthrough video [here](http://quick.as/wjreh4dm)

I did the test with 3 images (3264x2448 resolution) taken from my camera. I uploaded and inserted the photos on a Fork CMS page and used the compression module. I went from 8.2MB to 1.5MB for the three images together! 

## Installation

1. Upload the `/src/Backend/Modules/Compression` folder to your `/src/Backend/Modules` folder.
3. Browse to your Fork CMS backend.
4. Go to `Settings > Modules`. Click on the install button next to the menu module.
5. Go to `Settings > Modules > Compression` to use it.
6. Have fun!

## How to use it

1. Get a free API key (500 images/month) [here](https://tinypng.com/developers)
2. Go to `Settings > Modules > Compression` and enter your API key
3. Choose a few folders containing images to compress
4. Use a cronjob if you want to compress these images once in a while, or press the execute button to compress the images on the fly.

## To do

* Statistics (how many bytes are saved thanks to the module?)
* Option to overwrite or save a backup of the original images (e.g. with _backup suffix)

## Bugs

If you encounter any bugs, please create an issue and I'll try to fix it (or feel free to fix it yourself with a pull-request).

## Discussion
- Twitter: [@jessedobbelaere](https://www.twitter.com/jessedobbelaere)
- E-mail: <jesse@dobbelaere-ae.be> for any questions or remarks.

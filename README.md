# 🗜 Fork CMS Compression Module

![tests](https://github.com/friends-of-forkcms/fork-cms-module-compression/workflows/run-tests/badge.svg)
[![GitHub release](https://img.shields.io/github/release/friends-of-forkcms/fork-cms-module-compression.svg)](https://github.com/friends-of-forkcms/fork-cms-module-compression/releases/latest)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat)](http://makeapullrequest.com)

The Fork CMS Compression module lets you compress PNG & JPG images on your website. Use this module to shrink images on your website,
so they will use **less bandwidth and your website will load faster**. The compression module uses the compression engine
of [TinyPNG](https://tinypng.com/) and [TinyJPG](https://tinyjpg.com/).

---

## ✨ Highlights

Pick folders with images to compress:

[ ![Console window](https://imgur.com/gKQfz9d.png) ](https://imgur.com/gKQfz9d.png)

Execute image compression via the console window:

[ ![Console window](https://imgur.com/XAvZEje.gif) ](https://imgur.com/XAvZEje.gif)

See stats on the dashboard:

[ ![Statistics](https://i.imgur.com/yK5i1CV.png) ](https://i.imgur.com/yK5i1CV.png)

I did the test with 3 images (3264x2448 resolution) taken from my camera. I uploaded and inserted the photos on a Fork CMS page and used the compression module. The images total size went from 8.2MB to 1.5MB!

## 🔧 Getting started

1. Upload the `/src/Backend/Modules/Compression` folder to your `/src/Backend/Modules` folder.
2. Browse to your Fork CMS backend.
3. Go to `Settings > Modules`. Click on the install button next to the Compression module.
4. Go to `Settings > Modules > Compression` and enter your API key for TinyPNG. Get a **free** API key (500 images/month for free) [here](https://tinypng.com/developers).
5. Go to `Modules > Compression` and choose one or more folders with images to compress. Save your preference.
6. Go to the `Console` tab and start compressing images on the fly.

Note: We store a history of compressed files in the database with checksum. By doing that, we can ignore files that already have been compressed and skips them when a new compression task starts. This saves your TinyJPG api credits when doing multiple runs of the compression process.

## 🐛 Bugs

If you encounter any bugs, please create an issue (or feel free to fix it yourself with a PR).

## 💬 Discussion

- Slack: [Fork CMS Slack channel](https://fork-cms.herokuapp.com)
- Twitter: [@jessedobbelaere](https://www.twitter.com/jessedobbelaere)

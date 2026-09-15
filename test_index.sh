#!/bin/bash
sed -i 's/<?php/<?php throw new \\Exception("HIT INDEX");/' /opt/bonbon_kids/public/index.php
curl -s -I https://bonbonkw.com/
git -C /opt/bonbon_kids checkout public/index.php

#!/bin/bash
symfony server:stop
symfony server:start & yarn run encore dev --watch
#DON'T USE THIS! CURRENTLY UNDER DEVELOPMENT

## Add Module
```
git submodule add git@github.com:ninazu/framework.git ./vendor/ninazu/framework
git commit -m "#addSubModule"
git push
```

## FirstUpdateModule
```
cd ./vendor/ninazu/framework
git submodule update --init --recursive
git submodule update --recursive --remote
```

### PHPStorm Config
Add submodule directory
```
File -> Settings -> Version Control -> Add -> Directory
```
Checkout local branches
```
VCS -> Git -> Branches -> Repositories -> framework -> LocalBranches -> Checkout
```
Update project
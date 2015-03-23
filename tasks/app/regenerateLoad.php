<?
loader::generateCache();
print("Cache was generated, ".count(loader::getClassesList())." classes was found.");
<?php

namespace App\Controller\Admin;

use App\Entity\Assignment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AssignmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Assignment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('lesson'),
            TextField::new('code')->setHelp('Matches CLI IDs like BOOT-CLI-001'),
            TextField::new('title'),
            TextareaField::new('description')->hideOnIndex(),
            TextareaField::new('cliSteps')->hideOnIndex(),
            TextareaField::new('evaluationNotes')->hideOnIndex(),
            IntegerField::new('displayOrder'),
            DateTimeField::new('updatedAt')->hideOnForm(),
        ];
    }
}

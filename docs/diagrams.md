# CarnetPro Diagrams

Ce document contient un diagramme de classes et un diagramme de cas d'utilisation bases sur la structure actuelle du projet Laravel.

## Diagramme de classes

```mermaid
classDiagram
    class User {
        +id
        +name
        +email
        +password
        +is_global_admin
        +is_banned
        +reputation
        +hasActiveFlatshare() bool
        +isOwnerOf(flatshare) bool
    }

    class Flatshare {
        +id
        +name
        +status
        +owner_id
        +isActive() bool
    }

    class Membership {
        +id
        +user_id
        +flatshare_id
        +role
        +joined_at
        +left_at
        +isOwner() bool
    }

    class Invitation {
        +id
        +flatshare_id
        +email
        +token
        +status
        +expires_at
        +isPending() bool
        +isExpired() bool
    }

    class Category {
        +id
        +flatshare_id
        +name
        +icon
        +iconEmoji() string
        +iconLabel() string
        +ensureDefaultsForFlatshare(flatshareId)
    }

    class Expense {
        +id
        +flatshare_id
        +category_id
        +payer_id
        +title
        +amount
        +spent_at
    }

    class Payment {
        +id
        +flatshare_id
        +from_user_id
        +to_user_id
        +amount
        +settlement_amount
        +applied_amount
        +credit_amount
        +method
        +status
        +reference
        +note
        +paid_at
        +methodLabel() string
    }

    class Adjustment {
        +id
        +flatshare_id
        +from_user_id
        +to_user_id
        +amount
        +reason
    }

    class InvitationService {
        +create(flatshare, email)
        +accept(invitation, user)
        +refuse(invitation)
    }

    class SettlementService {
        +buildBalances(flatshare, month)
        +buildSettlements(flatshare, month)
        +buildExpenseStats(flatshare, month)
    }

    class ReputationService {
        +handleMemberLeave(flatshare, user)
        +handleMemberRemoval(flatshare, membership)
        +handleFlatshareCancellation(flatshare)
    }

    User "1" --> "0..*" Flatshare : owns
    User "1" --> "0..*" Membership : has
    User "1" --> "0..*" Expense : pays
    User "1" --> "0..*" Payment : sends
    User "1" --> "0..*" Payment : receives
    User "1" --> "0..*" Adjustment : source/target

    Flatshare "1" --> "1" User : owner
    Flatshare "1" --> "0..*" Membership : contains
    Flatshare "1" --> "0..*" Invitation : generates
    Flatshare "1" --> "0..*" Category : defines
    Flatshare "1" --> "0..*" Expense : contains
    Flatshare "1" --> "0..*" Payment : records
    Flatshare "1" --> "0..*" Adjustment : records

    Membership "*" --> "1" User : user
    Membership "*" --> "1" Flatshare : flatshare

    Invitation "*" --> "1" Flatshare : flatshare

    Category "*" --> "1" Flatshare : flatshare
    Category "1" --> "0..*" Expense : classifies

    Expense "*" --> "1" Flatshare : flatshare
    Expense "*" --> "0..1" Category : category
    Expense "*" --> "1" User : payer

    Payment "*" --> "1" Flatshare : flatshare
    Payment "*" --> "1" User : from_user
    Payment "*" --> "1" User : to_user

    Adjustment "*" --> "1" Flatshare : flatshare
    Adjustment "*" --> "1" User : from_user
    Adjustment "*" --> "1" User : to_user

    InvitationService ..> Invitation : manages
    InvitationService ..> Membership : creates
    InvitationService ..> Flatshare : uses
    SettlementService ..> Expense : aggregates
    SettlementService ..> Payment : subtracts
    SettlementService ..> Adjustment : applies
    ReputationService ..> Membership : updates
    ReputationService ..> Adjustment : creates
    ReputationService ..> User : changes reputation
```

## Diagramme de cas d'utilisation

```mermaid
flowchart LR
    Member[Member]
    Owner[Owner]
    Admin[Global Admin]

    UC1((S'inscrire))
    UC2((Se connecter))
    UC3((Gerer son profil))
    UC4((Rejoindre une colocation via invitation))
    UC5((Refuser une invitation))
    UC6((Voir les membres, roles et reputations))
    UC7((Ajouter une depense))
    UC8((Supprimer sa depense))
    UC9((Voir balances et settlements))
    UC10((Enregistrer un paiement))
    UC11((Quitter la colocation))

    UC12((Creer une colocation))
    UC13((Activer ou desactiver une colocation))
    UC14((Modifier la colocation))
    UC15((Annuler la colocation))
    UC16((Inviter un membre))
    UC17((Retirer un membre))
    UC18((Gerer les categories))

    UC19((Voir statistiques globales))
    UC20((Bannir un utilisateur))
    UC21((Debannir un utilisateur))

    Member --> UC1
    Member --> UC2
    Member --> UC3
    Member --> UC4
    Member --> UC5
    Member --> UC6
    Member --> UC7
    Member --> UC8
    Member --> UC9
    Member --> UC10
    Member --> UC11

    Owner --> UC12
    Owner --> UC13
    Owner --> UC14
    Owner --> UC15
    Owner --> UC16
    Owner --> UC17
    Owner --> UC18

    Admin --> UC19
    Admin --> UC20
    Admin --> UC21
    Admin --> UC13
    Admin --> UC15

    UC4 -. verification email/token .-> UC2
    UC4 -. si pas de compte .-> UC1
    UC9 -. calcule qui doit a qui .-> UC10
    UC15 -. impact reputation et dettes .-> UC11
```

## Notes

- `Owner` est un utilisateur standard qui devient administrateur de sa colocation.
- `Global Admin` peut aussi etre `Owner` ou `Member` dans une ou plusieurs colocations.
- Le projet applique une contrainte fonctionnelle importante: un utilisateur standard ne peut avoir qu'une seule colocation active a la fois.
- Les diagrammes representent l'etat actuel du code, y compris les paiements, categories avec icones, invitations par token et reputation.

<x-error-page
    code="419"
    title="Votre session a expiré."
    text="Veuillez réessayer."
    cta-label="Revenir"
    :cta-href="url()->previous()"
/>
